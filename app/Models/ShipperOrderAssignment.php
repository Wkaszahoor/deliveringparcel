<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ShipperOrderAssignment extends Model
{
    protected $fillable = [
        'order_id', 'request_id', 'shipper_profile_id',
        'shipper_fee', 'platform_fee', 'total_charged', 'status',
        'purchase_deadline', 'purchased_at', 'package_received_at',
        'dispatched_at', 'completed_at', 'wallet_credit_amount',
        'wallet_hold_amount', 'hold_release_at', 'admin_notes',
    ];

    protected $casts = [
        'purchase_deadline'    => 'datetime',
        'purchased_at'         => 'datetime',
        'package_received_at'  => 'datetime',
        'dispatched_at'        => 'datetime',
        'completed_at'         => 'datetime',
        'hold_release_at'      => 'datetime',
    ];

    public function order()
    {
        return $this->belongsTo(Orders::class, 'order_id');
    }

    public function request()
    {
        return $this->belongsTo(ShippingRequest::class, 'request_id');
    }

    public function shipper()
    {
        return $this->belongsTo(ShipperProfile::class, 'shipper_profile_id');
    }

    public function proofs()
    {
        return $this->hasMany(ShipperProof::class, 'assignment_id');
    }

    public function deliveryAddress()
    {
        return $this->hasOne(ShipperDeliveryAddress::class, 'assignment_id');
    }

    public function trackingDetails()
    {
        return $this->hasOne(ShipperTrackingDetail::class, 'assignment_id');
    }

    public function adminChat()
    {
        return $this->hasMany(ShipperAdminChat::class, 'assignment_id');
    }

    public function rating()
    {
        return $this->hasOne(ShipperRating::class, 'assignment_id');
    }

    public function isPurchaseOverdue(): bool
    {
        return $this->purchase_deadline !== null
            && now()->gt($this->purchase_deadline)
            && !in_array($this->status, [
                'purchased', 'package_received', 'proof_uploaded', 'proof_approved',
                'address_received', 'address_forwarded', 'dispatched',
                'tracking_added', 'tracking_shared', 'delivered', 'completed',
            ]);
    }
}
