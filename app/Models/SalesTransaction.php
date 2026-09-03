<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SalesTransaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'invoice_number',
        'transaction_date',
        'customer_name',
        'customer_phone',
        'sales_channel',
        'payment_method',
        'payment_status',
        'subtotal',
        'discount',
        'tax',
        'total_amount',
        'notes',
        'created_by',
    ];

    protected $casts = [
        'transaction_date' => 'date',
        'subtotal' => 'float',
        'discount' => 'float',
        'tax' => 'float',
        'total_amount' => 'float',
    ];

    public function items(): HasMany
    {
        return $this->hasMany(SalesTransactionItem::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function getFormattedTotalAttribute(): string
    {
        return 'Rp. ' . number_format($this->total_amount, 2, '.', '.');
    }

    public function getFormattedSubtotalAttribute(): string
    {
        return 'Rp. ' . number_format($this->subtotal, 2, '.', '.');
    }

    public function scopeFilterDate($query, $from, $to)
    {
        if ($from && $to) {
            $query->whereBetween('transaction_date', [$from, $to]);
        } elseif ($from) {
            $query->where('transaction_date', '>=', $from);
        } elseif ($to) {
            $query->where('transaction_date', '<=', $to);
        }
        return $query;
    }

    public function scopeSearch($query, $term)
    {
        if (!empty($term)) {
            $query->where(function ($q) use ($term) {
                $q->where('invoice_number', 'like', "%{$term}%")
                  ->orWhere('customer_name', 'like', "%{$term}%")
                  ->orWhere('sales_channel', 'like', "%{$term}%")
                  ->orWhere('payment_method', 'like', "%{$term}%");
            });
        }
        return $query;
    }
}
