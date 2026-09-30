<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ShipperQuote extends Model
{
    protected $fillable = [
        'request_id', 'shipper_profile_id', 'quoted_amount',
        'estimated_days', 'notes', 'status', 'admin_response', 'responded_at',
    ];

    protected $casts = [
        'responded_at' => 'datetime',
    ];

    public function request()
    {
        return $this->belongsTo(ShippingRequest::class, 'request_id');
    }

    public function shipper()
    {
        return $this->belongsTo(ShipperProfile::class, 'shipper_profile_id');
    }
}
