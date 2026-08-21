<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ClusteringResult extends Model
{
    use HasFactory;

    protected $fillable = [
        'clustering_analysis_id',
        'product_id',
        'product_name',
        'product_code',
        'category_name',
        'total_qty',
        'frequency',
        'total_revenue',
        'raw_lemon_kg',
        'normalized_vector',
        'cluster_index',
        'cluster_code',
        'cluster_label',
        'distance_to_centroid',
        'inventory_strategy',
    ];

    protected $casts = [
        'total_qty' => 'integer',
        'frequency' => 'integer',
        'total_revenue' => 'float',
        'raw_lemon_kg' => 'float',
        'normalized_vector' => 'array',
        'cluster_index' => 'integer',
        'distance_to_centroid' => 'float',
    ];

    public function analysis(): BelongsTo
    {
        return $this->belongsTo(ClusteringAnalysis::class, 'clustering_analysis_id');
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
