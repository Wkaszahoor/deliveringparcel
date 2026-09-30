<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WeightUnit extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'symbol',
        'grams',
        'is_default',
        'is_active',
    ];

    protected $casts = [
        'grams'     => 'integer',
        'is_default' => 'boolean',
        'is_active' => 'boolean',
    ];
}
