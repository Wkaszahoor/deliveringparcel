<?php

namespace Database\Seeders;

use App\Models\RateZone;
use App\Models\RateZoneCountry;
use App\Models\RateRule;
use App\Models\RateSurcharge;
use App\Models\RateInsurance;
use App\Models\GeoState;
use App\Models\GeoCity;
use App\Models\Country;
use Illuminate\Database\Seeder;

class RateSeeder extends Seeder
{
    public function run()
    {
        if (RateZone::exists()) {
            return;
        }

        /* Zones */
        $domestic = RateZone::create(['name' => 'US Domestic', 'code' => 'US-DOM', 'description' => 'United States domestic shipments']);
        $eu      = RateZone::create(['name' => 'Europe', 'code' => 'EU', 'description' => 'EU member states']);
        $asia    = RateZone::create(['name' => 'Asia', 'code' => 'ASIA', 'description' => 'Major Asian markets']);
        $uk      = RateZone::create(['name' => 'United Kingdom', 'code' => 'UK', 'description' => 'Great Britain & NI']);

        /* Map seeded countries to zones where the table exists */
        if (\Schema::hasTable('countries')) {
            $map = [
                'US-DOM' => ['US'],
                'EU'     => ['DE', 'FR', 'ES', 'IT', 'NL'],
                'ASIA'   => ['CN', 'JP', 'IN'],
                'UK'     => ['GB'],
            ];
            foreach ($map as $code => $iso2s) {
                $zone = RateZone::where('code', $code)->first();
                foreach ($iso2s as $iso) {
                    $country = Country::where('iso2', strtoupper($iso))->first();
                    if ($country) {
                        RateZoneCountry::create([
                            'rate_zone_id' => $zone->id,
                            'country_id'   => $country->id,
                            'country_code' => strtoupper($iso),
                        ]);
                    }
                }
            }
        }

        /* Rules */
        $sets = [
            [$domestic, $eu,   'standard', 0.5, 20, 12.00, 3.50, 5, 9],
            [$domestic, $eu,   'express',  0.5, 20, 24.00, 6.00, 2, 4],
            [$domestic, $asia, 'standard', 0.5, 30, 18.00, 4.50, 7, 14],
            [$domestic, $asia, 'economy',  0.5, 30, 14.00, 3.00, 10, 21],
            [$domestic, $uk,   'standard', 0.5, 20, 11.00, 3.20, 5, 8],
            [$eu,      $asia,  'priority', 0.5, 25, 28.00, 5.50, 3, 6],
        ];
        foreach ($sets as [$o, $d, $svc, $wmin, $wmax, $base, $kg, $tmin, $tmax]) {
            RateRule::create([
                'origin_zone_id' => $o->id, 'destination_zone_id' => $d->id,
                'service_type' => $svc, 'weight_min' => $wmin, 'weight_max' => $wmax,
                'base_price' => $base, 'per_kg_price' => $kg,
                'transit_days_min' => $tmin, 'transit_days_max' => $tmax,
                'is_active' => true, 'priority' => 0,
            ]);
        }

        /* Surcharges */
        RateSurcharge::create(['name' => 'Fuel surcharge', 'type' => 'percentage', 'value' => 12.5, 'applies_to' => 'fuel', 'is_active' => true]);
        RateSurcharge::create(['name' => 'Remote area delivery', 'type' => 'fixed', 'value' => 5.00, 'applies_to' => 'remote', 'is_active' => true]);

        /* Insurance tiers */
        RateInsurance::create(['declared_value_min' => 0,    'declared_value_max' => 100,  'cost' => 1.50, 'is_active' => true]);
        RateInsurance::create(['declared_value_min' => 100,  'declared_value_max' => 500,  'cost' => 4.00, 'is_active' => true]);
        RateInsurance::create(['declared_value_min' => 500,  'declared_value_max' => null, 'cost' => 9.00, 'is_active' => true]);

        /* Geo sample */
        if (\Schema::hasTable('geo_states')) {
            $us = \Schema::hasTable('countries') ? Country::where('iso2', 'US')->first() : null;
            $ca = GeoState::create(['country_id' => $us->id ?? null, 'country_code' => 'US', 'name' => 'California', 'code' => 'CA', 'is_active' => true]);
            $ny = GeoState::create(['country_id' => $us->id ?? null, 'country_code' => 'US', 'name' => 'New York', 'code' => 'NY', 'is_active' => true]);
            foreach (['Los Angeles', 'San Francisco', 'San Diego'] as $city) {
                GeoCity::create(['geo_state_id' => $ca->id, 'name' => $city, 'is_active' => true]);
            }
            foreach (['New York City', 'Buffalo'] as $city) {
                GeoCity::create(['geo_state_id' => $ny->id, 'name' => $city, 'is_active' => true]);
            }
        }
    }
}
