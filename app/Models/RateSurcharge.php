<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RateSurcharge extends Model
{
    protected $fillable = ['name', 'type', 'value', 'applies_to', 'is_active'];

    protected $casts = ['value' => 'float', 'is_active' => 'boolean'];
}
