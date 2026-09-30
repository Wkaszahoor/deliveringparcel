<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderChat extends Model
{
    use HasFactory;
    public $fillable = ['from', 'body', 'order_id','image','read'];
    
    public function orders()
    {
        // Exact class case — lowercase 'orders' 500s on Linux hosts.
        return $this->belongsTo(\App\Models\Orders::class);
    }
}
