<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'category_id',
        'code',
        'name',
        'slug',
        'description',
        'unit',
        'raw_lemon_requirement',
        'cost_price',
        'selling_price',
        'stock',
        'min_stock_alert',
        'image',
        'is_active',
    ];

    protected $casts = [
        'raw_lemon_requirement' => 'float',
        'cost_price' => 'float',
        'selling_price' => 'float',
        'stock' => 'integer',
        'min_stock_alert' => 'integer',
        'is_active' => 'boolean',
    ];

    protected static function boot()
    {
        parent::boot();
        static::creating(function ($product) {
            if (empty($product->slug)) {
                $product->slug = Str::slug($product->name);
            }
        });
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function transactionItems(): HasMany
    {
        return $this->hasMany(SalesTransactionItem::class);
    }

    public function getFormattedPriceAttribute(): string
    {
        return 'Rp ' . number_format($this->selling_price, 0, ',', '.');
    }

    public function getFormattedCostAttribute(): string
    {
        return 'Rp ' . number_format($this->cost_price, 0, ',', '.');
    }

    public function getProfitMarginAttribute(): float
    {
        return $this->selling_price - $this->cost_price;
    }

    public function getProfitMarginPercentAttribute(): float
    {
        if ($this->cost_price <= 0) return 0;
        return round((($this->selling_price - $this->cost_price) / $this->cost_price) * 100, 1);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeSearch($query, $term)
    {
        if (!empty($term)) {
            $query->where(function ($q) use ($term) {
                $q->where('name', 'like', "%{$term}%")
                  ->orWhere('code', 'like', "%{$term}%")
                  ->orWhere('description', 'like', "%{$term}%");
            });
        }
        return $query;
    }
}
