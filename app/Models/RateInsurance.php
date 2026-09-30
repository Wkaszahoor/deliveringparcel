<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RateInsurance extends Model
{
    protected $fillable = ['declared_value_min', 'declared_value_max', 'cost', 'is_active'];

    protected $casts = [
        'declared_value_min' => 'float',
        'declared_value_max' => 'float',
        'cost'               => 'float',
        'is_active'          => 'boolean',
    ];
}
