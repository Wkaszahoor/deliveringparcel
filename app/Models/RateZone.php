<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RateZone extends Model
{
    protected $fillable = ['name', 'code', 'description'];

    public function countries()
    {
        return $this->hasMany(RateZoneCountry::class);
    }

    public function rulesFrom()
    {
        return $this->hasMany(RateRule::class, 'origin_zone_id');
    }

    public function rulesTo()
    {
        return $this->hasMany(RateRule::class, 'destination_zone_id');
    }
}
