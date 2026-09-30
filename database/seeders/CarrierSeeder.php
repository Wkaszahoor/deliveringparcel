<?php

namespace Database\Seeders;

use App\Models\Carrier;
use Illuminate\Database\Seeder;

class CarrierSeeder extends Seeder
{
    public function run()
    {
        if (Carrier::exists()) {
            return;
        }
        foreach (config('admin_carriers.carriers', []) as $code => $meta) {
            Carrier::create(['code' => $code, 'name' => $meta['name'], 'is_enabled' => true]);
        }
    }
}
