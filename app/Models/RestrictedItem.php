<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RestrictedItem extends Model
{
    protected $fillable = ['name', 'category', 'severity', 'reason', 'requires_declaration'];

    protected $casts = ['requires_declaration' => 'boolean'];
}
