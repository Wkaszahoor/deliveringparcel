<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ShipperWalletTransaction extends Model
{
    protected $fillable = [
        'shipper_profile_id', 'type', 'amount', 'balance_after',
        'order_id', 'assignment_id', 'reference', 'status',
        'note', 'processed_by',
    ];

    public function shipper()
    {
        return $this->belongsTo(ShipperProfile::class, 'shipper_profile_id');
    }
}
