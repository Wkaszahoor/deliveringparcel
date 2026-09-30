<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WarehouseShipment extends Model
{
    protected $fillable = [
        'code', 'user_id', 'carrier_name', 'tracking_number', 'status',
        'dispatched_at', 'delivered_at', 'address_snapshot', 'notes',
    ];

    protected $casts = [
        'dispatched_at' => 'datetime',
        'delivered_at'  => 'datetime',
        'address_snapshot' => 'array',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function packages()
    {
        return $this->belongsToMany(WarehousePackage::class, 'package_shipment', 'shipment_id', 'package_id');
    }

    public function events()
    {
        return $this->hasMany(WarehouseShipmentEvent::class, 'shipment_id')->orderByDesc('created_at');
    }
}
