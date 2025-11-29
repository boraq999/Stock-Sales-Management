<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MarketerStock extends Model
{
    protected $table = 'marketer_stock';
    public $timestamps = false; // Based on migration, only updated_at exists? Migration says updated_at timestamp.

    protected $fillable = [
        'marketer_id',
        'product_id',
        'quantity',
    ];

    public function marketer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'marketer_id');
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
