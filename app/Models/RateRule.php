<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RateRule extends Model
{
    protected $fillable = [
        'origin_zone_id', 'destination_zone_id', 'service_type',
        'weight_min', 'weight_max', 'base_price', 'per_kg_price',
        'transit_days_min', 'transit_days_max', 'is_active', 'priority',
        'valid_from', 'valid_to',
    ];

    protected $casts = [
        'weight_min'    => 'float',
        'weight_max'    => 'float',
        'base_price'    => 'float',
        'per_kg_price'  => 'float',
        'is_active'     => 'boolean',
        'priority'      => 'integer',
        'valid_from'    => 'date',
        'valid_to'      => 'date',
    ];

    public function originZone()
    {
        return $this->belongsTo(RateZone::class, 'origin_zone_id');
    }

    public function destinationZone()
    {
        return $this->belongsTo(RateZone::class, 'destination_zone_id');
    }
}
