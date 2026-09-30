<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Shipping extends Model
{
    use HasFactory;
     public $fillable = ['user_id','shipping_name','shipping_email','shipping_phone','shipping_cargotype','shipping_country','shipping_Destination','shipping_Quantity','shipping_detail','shipping_Weight','shipping_Width','shipping_Height','order_id'];
}
