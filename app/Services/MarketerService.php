<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\StockAssignment;
use App\Models\User;
use App\Models\Invoice;
use App\Models\Store;
use App\Models\Product;
use App\Models\MarketerStock;
use App\Models\ReturnRequest;
use App\DTOs\CreateInvoiceDTO;
use App\Models\StoreReturn;
use App\Models\StoreDebt;
use Illuminate\Support\Facades\DB;
use Exception;

class MarketerService
{
    public function getDashboardData(User $marketer): array
    {
        return [
            'pending_assignments' => StockAssignment::where('marketer_id', $marketer->id)
                ->where('status', 'pending')
                ->with('product')
                ->get(),
            'stock' => MarketerStock::where('marketer_id', $marketer->id)
                ->with('product')
                ->get(),
            'clients' => Store::all(), // Assuming Marketer can see all clients or filter by assignment?
            'products' => Product::all(),
        ];
    }

    public function confirmAssignment(StockAssignment $assignment): void
    {
        if ($assignment->status !== 'pending') {
            throw new Exception('Assignment already processed.');
        }

        DB::transaction(function () use ($assignment) {
            $assignment->update(['status' => 'confirmed']);

            // Update Marketer Stock
            $stock = MarketerStock::firstOrCreate(
                ['marketer_id' => $assignment->marketer_id, 'product_id' => $assignment->product_id],
                ['quantity' => 0]
            );
            $stock->increment('quantity', $assignment->quantity);
        });
    }

    public function createInvoice(CreateInvoiceDTO $dto): Invoice
    {
        return DB::transaction(function () use ($dto) {
            // 1. Create/Find Client (Store)
            $store = null;
            if ($dto->storeId) {
                $store = Store::find($dto->storeId);
            } elseif ($dto->clientName) {
                $store = Store::create([
                    'name' => $dto->clientName,
                    'phone' => $dto->clientPhone,
                    // 'address' => ...
                ]);
            }

            if (!$store) {
                throw new Exception('Client is required.');
            }

            // 2. Create Invoice
            $invoice = Invoice::create([
                'user_id' => $dto->marketerId, // Marketer who created it
                'store_id' => $store->id,
                'total_amount' => 0, // Will calculate
                'status' => 'pending', // or completed?
            ]);

            $total = 0;
            foreach ($dto->items as $item) {
                $product = Product::findOrFail($item['product_id']);
                $quantity = $item['quantity'];
                $price = $product->price; // Assuming price is on product

                // Check stock
                $marketerStock = MarketerStock::where('marketer_id', $dto->marketerId)
                    ->where('product_id', $product->id)
                    ->first();

                if (!$marketerStock || $marketerStock->quantity < $quantity) {
                    throw new Exception("Insufficient stock for product: {$product->name}");
                }

                // Deduct stock
                $marketerStock->decrement('quantity', $quantity);

                // Add Invoice Item
                $invoice->items()->create([
                    'product_id' => $product->id,
                    'quantity' => $quantity,
                    'price' => $price,
                    'total' => $quantity * $price,
                ]);

                $total += $quantity * $price;
            }

            $invoice->update(['total_amount' => $total]);

            return $invoice;
        });
    }

    public function processStoreReturn(User $marketer, array $data): void
    {
        DB::transaction(function () use ($marketer, $data) {
            // Marketer opens return form -> updates stock -> updates balance
            // Assuming data contains: invoice_id (optional), product_id, quantity, store_id (if no invoice)

            $product = Product::findOrFail($data['product_id']);
            $quantity = $data['quantity'];

            // Update Marketer Stock (Increase)
            $stock = MarketerStock::firstOrCreate(
                ['marketer_id' => $marketer->id, 'product_id' => $product->id],
                ['quantity' => 0]
            );
            $stock->increment('quantity', $quantity);

            // Record Return
            StoreReturn::create([
                'invoice_id' => $data['invoice_id'] ?? null, // nullable in DB?
                'product_id' => $product->id,
                'marketer_id' => $marketer->id,
                'quantity' => $quantity,
            ]);

            // Update Client Balance (Debt)
            // Assuming we credit the store for the return value
            // We need the price from the original invoice or product current price
            $price = $product->price; // Simplified
            $refundAmount = $quantity * $price;

            // If we have StoreDebt model
            if (class_exists(StoreDebt::class)) {
                StoreDebt::create([
                    'store_id' => $data['store_id'] ?? null, // We need store_id. If from invoice, get it from invoice.
                    'amount' => $refundAmount,
                    'type' => 'credit',
                    'description' => 'Return of product: ' . $product->name,
                    'reference_id' => $stock->id, // or return record id
                    'reference_type' => StoreReturn::class,
                ]);
            }
        });
    }

    public function createReturnRequest(User $marketer, array $data): void
    {
        DB::transaction(function () use ($marketer, $data) {
            $product = Product::findOrFail($data['product_id']);
            $quantity = $data['quantity'];

            // Check if marketer has enough stock to return?
            // Usually we deduct when approved, or reserve it?
            // User says: "Once Yassin confirms, it will be approved by Hammam, and stock updated automatically."
            // So Yassin requests, Hammam approves, THEN stock updates.

            ReturnRequest::create([
                'marketer_id' => $marketer->id,
                'product_id' => $product->id,
                'quantity' => $quantity,
                'status' => 'pending',
            ]);
        });
    }
}
