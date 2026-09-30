<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Claim extends Model
{
    protected $fillable = [
        'return_request_id', 'order_id', 'user_id', 'type', 'amount_claimed',
        'description', 'evidence', 'status', 'payout', 'closed_at',
    ];

    protected $casts = [
        'evidence'     => 'array',
        'amount_claimed' => 'float',
        'payout'       => 'float',
        'closed_at'    => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function returnRequest()
    {
        return $this->belongsTo(ReturnRequest::class);
    }
}
