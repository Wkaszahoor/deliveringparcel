<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Orderss extends Model
{
    use HasFactory;
    public $fillable = ['shipfrom','shipto','postalcode','address',
                        'approximate_weight','product_services',
                        'user_id','product_photo','product_customs',
                        'product_check','product_prohibited','product_disinfection',
                        'product_consolidation','product_services','product_purchase','total','order_id',
                        'active_tab', 'companyname','edit_offer', 'product_totalprice','product_total','confirmation','tracking_status','trackingid','trackinglink', 'order_status','custom_status', 
                        'custom_category','custom_total_value', 'custom_total_weight','custom_total_quantity',
                        'ship_name', 'ship_address1', 'ship_address2', 'ship_city', 'ship_state', 'ship_postalcode', 'ship_country', 'product_description', 'ship_number'];
    
    public function orderproducts()
    {
        return $this->hasMany('App\Models\orderproducts');
    }
    public function offerneedaddresses()
    {
        return $this->hasMany('App\Models\offerneedaddresses');
    }
    public function order_chats()
    {
        return $this->hasMany('App\Models\OrderChat');
    }
}
