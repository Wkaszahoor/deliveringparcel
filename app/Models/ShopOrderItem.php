<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ShopOrderItem extends Model
{
    protected $fillable = ['shop_order_id', 'shop_product_id', 'name', 'qty', 'price'];

    protected $casts = ['qty' => 'integer', 'price' => 'float'];
}
