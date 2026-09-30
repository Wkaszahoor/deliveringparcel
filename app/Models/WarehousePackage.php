<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WarehousePackage extends Model
{
    protected $fillable = [
        'user_id', 'expected_tracking', 'received_at', 'status',
        'storage_bin_id', 'notes', 'photos', 'created_by',
    ];

    protected $casts = ['received_at' => 'datetime', 'photos' => 'array'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function bin()
    {
        return $this->belongsTo(WarehouseBin::class, 'storage_bin_id');
    }

    public function shipments()
    {
        return $this->belongsToMany(WarehouseShipment::class, 'package_shipment', 'package_id', 'shipment_id');
    }
}
