<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ConsentTemplate extends Model
{
    protected $fillable = [
        'name', 'slug', 'body', 'version', 'is_active',
        'trigger_type', 'trigger_value', 'requires_signature',
    ];

    protected $casts = ['version' => 'integer', 'is_active' => 'boolean', 'requires_signature' => 'boolean'];
}
