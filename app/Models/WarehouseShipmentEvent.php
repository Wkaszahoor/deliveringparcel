<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WarehouseShipmentEvent extends Model
{
    protected $fillable = ['shipment_id', 'status', 'note'];

    public function shipment()
    {
        return $this->belongsTo(WarehouseShipment::class, 'shipment_id');
    }
}
