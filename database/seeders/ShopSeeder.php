<?php

namespace Database\Seeders;

use App\Models\ShopCategory;
use App\Models\ShopProduct;
use App\Models\ShopCoupon;
use Illuminate\Database\Seeder;

class ShopSeeder extends Seeder
{
    public function run()
    {
        if (ShopCategory::exists()) {
            return;
        }

        $packing = ShopCategory::create(['name' => 'Packing Supplies', 'slug' => 'packing-supplies']);
        $access  = ShopCategory::create(['name' => 'Accessories', 'slug' => 'accessories']);
        $bubble  = ShopCategory::create(['name' => 'Bubble Wrap & Protection', 'slug' => 'bubble-wrap', 'parent_id' => $packing->id]);

        $products = [
            [$packing->id, 'Cardboard Shipping Box (Medium)', 'BOX-M', 2.49, 200],
            [$bubble->id,  'Bubble Wrap Roll 50m', 'BWR-50', 12.99, 80],
            [$access->id,  'Digital Postal Scale 30kg', 'SCALE-30', 39.00, 25],
            [$access->id,  'Reinforced Packing Tape (6-pack)', 'TAPE-6', 8.50, 150],
            [$packing->id, 'Heavy Duty Mailer Envelopes (100)', 'MAIL-100', 15.75, 60],
        ];
        foreach ($products as [$cat, $name, $sku, $price, $stock]) {
            ShopProduct::create([
                'name' => $name, 'slug' => \Str::slug($name), 'sku' => $sku,
                'shop_category_id' => $cat,
                'description' => $name . ' — warehouse-grade quality for international parcel forwarding.',
                'price' => $price, 'stock' => $stock,
                'is_active' => true, 'featured' => false,
            ]);
        }

        ShopCoupon::create(['code' => 'WELCOME10', 'type' => 'percent', 'value' => 10, 'min_order' => 25, 'is_active' => true]);
        ShopCoupon::create(['code' => 'SHIP5', 'type' => 'fixed', 'value' => 5, 'is_active' => true, 'usage_limit' => 100]);
    }
}
