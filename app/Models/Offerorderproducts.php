<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Offerorderproducts extends Model
{
    use HasFactory;
     public $fillable = ['productname','offer_id','producturl', 'productquantity','trackingid','image','productprice','product_total','productspread','trackinglink'];
    public function offerorder()
    {
        // Exact class case — lowercase 'offerorder' 500s on Linux hosts.
        return $this->belongsTo(\App\Models\Offerorder::class);
    }
}
