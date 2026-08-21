<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RawLemonStock extends Model
{
    use HasFactory;

    protected $fillable = [
        'period_month',
        'status',
        'quantity_kg',
        'inbound_kg',
        'used_in_production_kg',
        'waste_kg',
        'notes',
    ];

    protected $casts = [
        'quantity_kg' => 'float',
        'inbound_kg' => 'float',
        'used_in_production_kg' => 'float',
        'waste_kg' => 'float',
    ];
}
