<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Orderproducts extends Model
{
    use HasFactory;
    public $fillable = ['productname','order_id','producturl', 'productweight', 'productquantity','productprice','trackinglink','product_total','trackingid','image','receipt',
    'custom_weight',
        'custom_value', 'product_description',
        'custom_category'];
    public function orders()
    {
        // Exact class case — lowercase 'orders' 500s on Linux hosts.
        return $this->belongsTo(\App\Models\Orders::class);
    }
}
