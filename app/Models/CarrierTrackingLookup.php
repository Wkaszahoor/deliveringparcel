<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CarrierTrackingLookup extends Model
{
    protected $fillable = ['number', 'carrier_code', 'result_status'];
}
