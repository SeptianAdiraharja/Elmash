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
        'transaction_date',
        'day_name',
        'x1_dried_lemon_kg',
        'x2_manisan_lemon_pouch',
        'x3_sari_lemon_liter',
        'normalized_vector',
        'cluster_index',
        'cluster_code',
        'cluster_label',
        'distance_to_centroid',
        'inventory_strategy',
    ];

    protected $casts = [
        'transaction_date' => 'date',
        'x1_dried_lemon_kg' => 'integer',
        'x2_manisan_lemon_pouch' => 'integer',
        'x3_sari_lemon_liter' => 'integer',
        'normalized_vector' => 'array',
        'cluster_index' => 'integer',
        'distance_to_centroid' => 'float',
    ];

    public function analysis(): BelongsTo
    {
        return $this->belongsTo(ClusteringAnalysis::class, 'clustering_analysis_id');
    }
}