<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class KycVerification extends Model
{
    protected $fillable = [
        'user_id', 'doc_type', 'doc_number', 'doc_country', 'files',
        'status', 'rejection_reason', 'reviewed_by', 'reviewed_at', 'expires_at',
    ];

    protected $casts = [
        'files'       => 'array',
        'reviewed_at' => 'datetime',
        'expires_at'  => 'date',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
