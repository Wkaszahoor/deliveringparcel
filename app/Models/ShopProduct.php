<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ShopProduct extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name', 'slug', 'sku', 'shop_category_id', 'description',
        'price', 'compare_price', 'stock', 'images', 'is_active', 'featured',
        'forced_payment_method_code',
    ];

    protected $casts = [
        'price'         => 'float',
        'compare_price' => 'float',
        'stock'         => 'integer',
        'images'        => 'array',
        'is_active'     => 'boolean',
        'featured'      => 'boolean',
    ];

    public function category()
    {
        return $this->belongsTo(ShopCategory::class, 'shop_category_id');
    }

    public function reviews()
    {
        return $this->hasMany(ShopReview::class);
    }

    public function firstImage()
    {
        return $this->images[0] ?? null;
    }
}
