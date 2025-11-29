<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StoreDebt extends Model
{
    protected $fillable = [
        'store_id',
        'amount',
        'type', // 'debit' (owed) or 'credit' (paid/returned)
        'description',
        'reference_id', // e.g., invoice_id
        'reference_type', // e.g., Invoice::class
    ];

    public function store(): BelongsTo
    {
        return $this->belongsTo(Store::class);
    }
}
