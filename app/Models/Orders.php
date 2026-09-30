<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Orders extends Model
{
    use HasFactory;

    /**
     * Route model binding key — ALWAYS the database primary key (id).
     * The orders.order_id column is the human-facing display number
     * (e.g. "1258") and must NEVER be used for route binding or
     * API navigation.  Mobile apps pass this PK as "orderId" / "id".
     */
    public function getRouteKeyName(): string
    {
        return 'id';
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function offers()
    {
        return $this->hasMany(Offerorder::class, 'order_id');
    }
    public $fillable = ['shipfrom','shipto','postalcode','address',
                        'approximate_weight','product_services',
                        'user_id','product_photo','product_customs',
                        'product_check','product_prohibited','product_disinfection',
                        'product_consolidation','product_services','product_purchase','total','order_id',
                        'active_tab', 'companyname','edit_offer', 'product_totalprice','product_total','confirmation','tracking_status','trackingid','trackinglink', 'order_status','custom_status', 
                        'custom_category','custom_total_value', 'custom_total_weight','custom_total_quantity',
                        'ship_name', 'ship_address1', 'ship_address2', 'ship_city', 'ship_state', 'ship_postalcode', 'ship_country', 'product_description', 'ship_number', 'archived_at',
                        'forced_payment_method_code'];
    
    public function orderproducts()
    {
        // CRITICAL: class name MUST match the file the autoloader finds on
        // Linux (case-sensitive). If the model file is Orderproducts.php then
        // 'App\\Models\\orderproducts' (lowercase 'p') 500s on prod. The
        // project's actual model class is Orderproducts (capital O) — use that.
        return $this->hasMany(\App\Models\Orderproducts::class, 'order_id');
    }
    public function offerneedaddresses()
    {
        // FK is order_id — must be explicit or Eloquent guesses orders_id (wrong)
        return $this->hasMany('App\Models\offerneedaddresses', 'order_id');
    }
    public function order_chats()
    {
        // FK is order_id — must be explicit or Eloquent guesses orders_id (wrong)
        return $this->hasMany('App\Models\OrderChat', 'order_id');
    }
}
