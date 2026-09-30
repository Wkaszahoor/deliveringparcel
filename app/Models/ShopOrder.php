<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ShopOrder extends Model
{
    protected $fillable = ['code', 'user_id', 'total', 'status', 'paid_at', 'ship_name', 'ship_address', 'ship_phone', 'forced_payment_method_code'];

    protected $casts = ['total' => 'float', 'paid_at' => 'datetime'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function items()
    {
        return $this->hasMany(ShopOrderItem::class);
    }
}
