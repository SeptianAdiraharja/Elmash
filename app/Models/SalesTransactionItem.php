<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SalesTransactionItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'sales_transaction_id',
        'product_id',
        'product_name',
        'product_code',
        'quantity',
        'unit_price',
        'cost_price',
        'subtotal',
        'raw_lemon_used',
    ];

    protected $casts = [
        'quantity' => 'integer',
        'unit_price' => 'float',
        'cost_price' => 'float',
        'subtotal' => 'float',
        'raw_lemon_used' => 'float',
    ];

    public function transaction(): BelongsTo
    {
        return $this->belongsTo(SalesTransaction::class, 'sales_transaction_id');
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
