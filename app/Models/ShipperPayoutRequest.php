<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ShipperPayoutRequest extends Model
{
    protected $fillable = [
        'shipper_profile_id', 'amount', 'method', 'payment_details',
        'status', 'admin_notes', 'processed_by', 'processed_at',
    ];

    protected $casts = [
        'processed_at' => 'datetime',
    ];

    public function shipper()
    {
        return $this->belongsTo(ShipperProfile::class, 'shipper_profile_id');
    }

    public function processedBy()
    {
        return $this->belongsTo(User::class, 'processed_by');
    }
}
