<?php

namespace Database\Seeders;

use App\Models\WeightUnit;
use Illuminate\Database\Seeder;

class WeightUnitSeeder extends Seeder
{
    public function run()
    {
        $units = [
            ['name' => 'Kilogram', 'symbol' => 'kg', 'grams' => 1000, 'is_default' => true,  'is_active' => true],
            ['name' => 'Gram',     'symbol' => 'g',  'grams' => 1,    'is_default' => false, 'is_active' => true],
            ['name' => 'Pound',    'symbol' => 'lb', 'grams' => 454,  'is_default' => false, 'is_active' => true],
            ['name' => 'Ounce',    'symbol' => 'oz', 'grams' => 28,   'is_default' => false, 'is_active' => true],
        ];

        foreach ($units as $data) {
            WeightUnit::firstOrCreate(['symbol' => $data['symbol']], $data);
        }
    }
}
