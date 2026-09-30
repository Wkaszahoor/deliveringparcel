<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ShopCoupon extends Model
{
    protected $fillable = [
        'code', 'type', 'value', 'min_order', 'starts_at', 'ends_at',
        'usage_limit', 'used_count', 'is_active',
    ];

    protected $casts = [
        'value'       => 'float',
        'min_order'   => 'float',
        'starts_at'   => 'datetime',
        'ends_at'     => 'datetime',
        'usage_limit' => 'integer',
        'used_count'  => 'integer',
        'is_active'   => 'boolean',
    ];
}
