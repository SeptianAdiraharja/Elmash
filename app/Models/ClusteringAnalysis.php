<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ClusteringAnalysis extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'period_start',
        'period_end',
        'k_value',
        'max_iterations',
        'iterations_count',
        'is_converged',
        'sse_inertia',
        'davies_bouldin_index',
        'features',
        'initial_centroids',
        'final_centroids',
        'cluster_summary',
        'raw_data_snapshot',
        'iteration_history',
        'notes',
        'created_by',
    ];

    protected $casts = [
        'period_start' => 'date',
        'period_end' => 'date',
        'k_value' => 'integer',
        'max_iterations' => 'integer',
        'iterations_count' => 'integer',
        'is_converged' => 'boolean',
        'sse_inertia' => 'float',
        'davies_bouldin_index' => 'float',
        'features' => 'array',
        'initial_centroids' => 'array',
        'final_centroids' => 'array',
        'cluster_summary' => 'array',
        'raw_data_snapshot' => 'array',
        'iteration_history' => 'array',
    ];

    public function results(): HasMany
    {
        return $this->hasMany(ClusteringResult::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
