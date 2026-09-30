<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VatRule extends Model
{
    protected $fillable = ['scheme', 'country_code', 'rate', 'registration_number', 'is_active', 'notes'];

    protected $casts = ['rate' => 'float', 'is_active' => 'boolean'];
}
