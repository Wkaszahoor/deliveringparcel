<?php

namespace Database\Seeders;

use App\Models\Country;
use Illuminate\Database\Seeder;

class CountrySeeder extends Seeder
{
    public function run()
    {
        $countries = [
            ['name' => 'United States', 'iso2' => 'US', 'iso3' => 'USA', 'dial_code' => '+1',   'shipping_rate' => 12.50],
            ['name' => 'United Kingdom', 'iso2' => 'GB', 'iso3' => 'GBR', 'dial_code' => '+44',  'shipping_rate' => 14.00],
            ['name' => 'Canada',        'iso2' => 'CA', 'iso3' => 'CAN', 'dial_code' => '+1',   'shipping_rate' => 15.25],
            ['name' => 'Australia',     'iso2' => 'AU', 'iso3' => 'AUS', 'dial_code' => '+61',  'shipping_rate' => 18.75],
            ['name' => 'Germany',       'iso2' => 'DE', 'iso3' => 'DEU', 'dial_code' => '+49',  'shipping_rate' => 13.40],
            ['name' => 'France',        'iso2' => 'FR', 'iso3' => 'FRA', 'dial_code' => '+33',  'shipping_rate' => 13.40],
            ['name' => 'Spain',         'iso2' => 'ES', 'iso3' => 'ESP', 'dial_code' => '+34',  'shipping_rate' => 12.90],
            ['name' => 'Italy',         'iso2' => 'IT', 'iso3' => 'ITA', 'dial_code' => '+39',  'shipping_rate' => 13.10],
            ['name' => 'United Arab Emirates', 'iso2' => 'AE', 'iso3' => 'ARE', 'dial_code' => '+971', 'shipping_rate' => 21.00],
            ['name' => 'India',         'iso2' => 'IN', 'iso3' => 'IND', 'dial_code' => '+91',  'shipping_rate' => 16.80],
            ['name' => 'China',         'iso2' => 'CN', 'iso3' => 'CHN', 'dial_code' => '+86',  'shipping_rate' => 17.30],
            ['name' => 'Mexico',        'iso2' => 'MX', 'iso3' => 'MEX', 'dial_code' => '+52',  'shipping_rate' => 14.60, 'is_active' => false],
        ];

        foreach ($countries as $data) {
            Country::firstOrCreate(
                ['iso2' => $data['iso2']],
                $data + ['is_active' => true]
            );
        }
    }
}
