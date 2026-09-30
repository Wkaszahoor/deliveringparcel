<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ShopReview extends Model
{
    protected $fillable = ['shop_product_id', 'user_id', 'rating', 'title', 'body', 'is_approved'];

    protected $casts = ['rating' => 'integer', 'is_approved' => 'boolean'];

    public function product()
    {
        return $this->belongsTo(ShopProduct::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
