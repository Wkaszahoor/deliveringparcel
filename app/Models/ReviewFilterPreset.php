<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * RV-005: saved combined-filter presets for the admin reviews table
 * (seeded defaults live in config/admin_reviews.presets).
 */
class ReviewFilterPreset extends Model
{
    protected $fillable = ['name', 'filters'];

    protected $casts = ['filters' => 'array'];
}
