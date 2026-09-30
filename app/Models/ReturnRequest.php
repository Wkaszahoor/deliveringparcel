<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ReturnRequest extends Model
{
    protected $fillable = [
        'order_id', 'user_id', 'reason', 'description', 'status',
        'resolution_note', 'handled_by', 'handled_at',
    ];

    protected $casts = ['handled_at' => 'datetime'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function order()
    {
        return $this->belongsTo(Orders::class, 'order_id');
    }

    public function claims()
    {
        return $this->hasMany(Claim::class);
    }
}
