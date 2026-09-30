<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ShipperCountryRequest extends Model
{
    protected $table = 'shipper_country_requests';

    protected $guarded = ['id'];

    protected $casts = [
        'countries'   => 'array',
        'reviewed_at' => 'datetime',
    ];

    public function profile()
    {
        return $this->belongsTo(ShipperProfile::class, 'shipper_profile_id');
    }
}
