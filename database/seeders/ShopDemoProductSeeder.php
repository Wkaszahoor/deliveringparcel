<?php

namespace Database\Seeders;

use App\Models\ShopProduct;
use Illuminate\Database\Seeder;

class ShopDemoProductSeeder extends Seeder
{
    public function run()
    {
        if (ShopProduct::where('is_active', true)->where('stock', '>', 0)->exists()) {
            return;
        }

        $items = [
            ['name' => 'Cardboard Box M (40×30×25)', 'sku' => 'BOX-M-01', 'price' => 2.40, 'stock' => 250, 'featured' => true,
             'description' => 'Double-walled cardboard box, ideal for books and electronics. Ships flat.'],
            ['name' => 'Bubble wrap roll (5 m)', 'sku' => 'BWR-5M', 'price' => 4.90, 'stock' => 120, 'featured' => true,
             'description' => 'Small-bubble protective wrap, 50 cm wide, perforated every 30 cm.'],
            ['name' => 'Reinforced packing tape (6 rolls)', 'sku' => 'TAPE-6', 'price' => 7.20, 'stock' => 80, 'featured' => false,
             'description' => '48 mm x 100 m clear tape with strong acrylic adhesive.'],
        ];

        foreach ($items as $i) {
            ShopProduct::create([
                'name'        => $i['name'],
                'slug'        => \Str::slug($i['name']),
                'sku'         => $i['sku'],
                'description' => $i['description'],
                'price'       => $i['price'],
                'stock'       => $i['stock'],
                'images'      => [],
                'is_active'   => true,
                'featured'    => $i['featured'],
            ]);
        }
    }
}
