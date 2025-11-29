<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Services\MarketerService;
use App\Models\StockAssignment;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class MarketerController extends Controller
{
    private MarketerService $service;

    public function __construct(MarketerService $service)
    {
        $this->service = $service;
    }

    public function index(): View
    {
        $data = $this->service->getDashboardData(auth()->user());
        return view('marketer.index', $data);
    }

    public function confirmAssignment(StockAssignment $assignment): RedirectResponse
    {
        try {
            // Ensure the assignment belongs to the logged-in marketer
            if ($assignment->marketer_id !== auth()->id()) {
                abort(403);
            }

            $this->service->confirmAssignment($assignment);
            return redirect()->back()->with('success', 'Assignment confirmed successfully.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    public function storeInvoice(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'client_name' => 'required_without:store_id|string|max:255',
            'client_phone' => 'nullable|string|max:20',
            'store_id' => 'nullable|exists:stores,id',
            'items' => 'required|array',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1',
        ]);

        try {
            $dto = \App\DTOs\CreateInvoiceDTO::fromRequest($data, auth()->id());
            $invoice = $this->service->createInvoice($dto);
            // Send WhatsApp logic would go here or be triggered by an event
            return redirect()->back()->with('success', 'Invoice created successfully.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    public function storeReturn(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'product_id' => 'required|exists:products,id',
            'quantity' => 'required|integer|min:1',
            'invoice_id' => 'nullable|exists:invoices,id',
        ]);

        try {
            $this->service->processStoreReturn(auth()->user(), $data);
            return redirect()->back()->with('success', 'Return processed successfully.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    public function warehouseReturn(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'product_id' => 'required|exists:products,id',
            'quantity' => 'required|integer|min:1',
        ]);

        try {
            $this->service->createReturnRequest(auth()->user(), $data);
            return redirect()->back()->with('success', 'Return request sent to warehouse.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }
}
