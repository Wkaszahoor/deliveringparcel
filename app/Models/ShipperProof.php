<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ShipperProof extends Model
{
    protected $fillable = [
        'assignment_id', 'shipper_profile_id', 'proof_type',
        'file_path', 'mime_type', 'file_size', 'admin_approved',
        'customer_visible', 'admin_approved_at', 'admin_approved_by',
        'admin_notes', 'shipper_notes',
    ];

    protected $casts = [
        'admin_approved'   => 'boolean',
        'customer_visible' => 'boolean',
        'admin_approved_at' => 'datetime',
        'file_size'        => 'integer',
    ];

    public function assignment()
    {
        return $this->belongsTo(ShipperOrderAssignment::class, 'assignment_id');
    }

    public function shipper()
    {
        return $this->belongsTo(ShipperProfile::class, 'shipper_profile_id');
    }

    public function scopeApproved($q)
    {
        return $q->where('admin_approved', true);
    }

    public function scopeVisibleToCustomer($q)
    {
        return $q->where('customer_visible', true);
    }
}
