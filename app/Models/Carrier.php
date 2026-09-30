<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Carrier extends Model
{
    protected $fillable = ['code', 'name', 'is_enabled', 'settings', 'last_checked_at'];

    protected $casts = ['is_enabled' => 'boolean', 'settings' => 'array', 'last_checked_at' => 'datetime'];
}
