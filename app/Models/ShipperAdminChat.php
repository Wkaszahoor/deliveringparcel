<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ShipperAdminChat extends Model
{
    // Table name is singular (matches the migration) — Eloquent would
    // otherwise guess the plural and break every query.
    protected $table = 'shipper_admin_chat';

    protected $fillable = [
        'assignment_id', 'request_id', 'order_id',
        'from_type', 'from_id', 'message', 'attachment_path',
        'is_read', 'read_at',
    ];

    protected $casts = [
        'is_read'  => 'boolean',
        'read_at'  => 'datetime',
    ];

    public function assignment()
    {
        return $this->belongsTo(ShipperOrderAssignment::class, 'assignment_id');
    }

    public function request()
    {
        return $this->belongsTo(ShippingRequest::class, 'request_id');
    }
}
