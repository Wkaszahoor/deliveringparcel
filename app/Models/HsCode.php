<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HsCode extends Model
{
    protected $fillable = ['code', 'description', 'category', 'duty_hint'];

    protected $casts = ['duty_hint' => 'float'];
}
