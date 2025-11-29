<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StoreDebtPayment extends Model
{
    protected $fillable = [
        'store_id',
        'amount',
        'payment_method',
        'transaction_id',
    ];

    public function store(): BelongsTo
    {
        return $this->belongsTo(Store::class);
    }
}
