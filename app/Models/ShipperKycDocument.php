<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ShipperKycDocument extends Model
{
    protected $fillable = [
        'shipper_profile_id', 'document_type', 'file_path',
        'original_filename', 'status', 'reviewed_by', 'review_notes',
        'reviewed_at',
    ];

    protected $casts = [
        'reviewed_at' => 'datetime',
    ];

    public function shipperProfile()
    {
        return $this->belongsTo(ShipperProfile::class, 'shipper_profile_id');
    }
}
