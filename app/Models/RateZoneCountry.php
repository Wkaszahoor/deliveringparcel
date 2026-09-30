<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RateZoneCountry extends Model
{
    public $timestamps = false;

    protected $fillable = ['rate_zone_id', 'country_id', 'country_code'];
}
