<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ShipperTrackingDetail extends Model
{
    protected $fillable = [
        'assignment_id', 'order_id', 'carrier', 'tracking_number',
        'tracking_url', 'ship_date', 'estimated_delivery',
        'admin_reviewed', 'shared_with_customer', 'shared_at',
        'shared_by', 'shipper_notes', 'admin_notes',
    ];

    protected $casts = [
        'ship_date'             => 'date',
        'estimated_delivery'    => 'date',
        'admin_reviewed'        => 'boolean',
        'shared_with_customer'  => 'boolean',
        'shared_at'             => 'datetime',
    ];

    public function assignment()
    {
        return $this->belongsTo(ShipperOrderAssignment::class, 'assignment_id');
    }
}
