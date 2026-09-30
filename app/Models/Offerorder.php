<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Offerorder extends Model
{
    use HasFactory;

    public function order()
    {
        return $this->belongsTo(Orders::class, 'order_id');
    }
    public $fillable = ['shipingaddress','shippingaddress_id','description','total','order_id','offer_status','product_total','rejections_note'];

    public function offerorderservices()
    {
        // FK is offer_id — must be explicit or Eloquent guesses offerorder_id (wrong).
        // Class case must match the file EXACTLY: lowercase variants autoload on
        // Windows but 500 on Linux (case-sensitive FS) — this was the offer 500.
        return $this->hasMany(\App\Models\Offerorderservices::class, 'offer_id');
    }
    public function offerorderproducts()
    {
        // FK is offer_id — must be explicit or Eloquent guesses offerorder_id (wrong).
        return $this->hasMany(\App\Models\Offerorderproducts::class, 'offer_id');
    }
}
