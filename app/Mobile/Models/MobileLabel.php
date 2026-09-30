<?php

namespace App\Mobile\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class MobileLabel extends Model
{
    protected $table = 'mobile_labels';

    protected $fillable = ['label_key', 'label_value', 'description'];

    /** The 10 factory-default label values. */
    public static function defaults(): array
    {
        return [
            'mobile_label_order'             => 'Order Detail',
            'mobile_label_shipping'          => 'Shipping Detail',
            'mobile_label_products'          => 'Product List',
            'mobile_label_tracking'          => 'Tracking',
            'mobile_label_tracking_customer' => 'Customer Provided Tracking',
            'mobile_label_tracking_admin'    => 'Admin Provided Tracking',
            'mobile_label_services'          => 'Services',
            'mobile_label_status'            => 'Change Status',
            'mobile_label_offer'             => 'Offer',
            'mobile_label_chat'              => 'Chat',
        ];
    }

    /** All labels as key => value (cached). */
    public static function allKeyed(): array
    {
        return Cache::remember('mobile_labels', config('mobile.cache_ttl.labels', 600), function () {
            return static::query()->pluck('label_value', 'label_key')->toArray();
        });
    }

    /** Reset every label to its factory default. */
    public static function resetToDefaults(): void
    {
        foreach (static::defaults() as $key => $value) {
            static::updateOrCreate(['label_key' => $key], ['label_value' => $value]);
        }

        Cache::forget('mobile_labels');
    }

    protected static function booted()
    {
        static::saved(fn () => Cache::forget('mobile_labels'));
        static::deleted(fn () => Cache::forget('mobile_labels'));
    }
}
