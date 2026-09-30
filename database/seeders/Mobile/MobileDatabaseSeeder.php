<?php

namespace Database\Seeders\Mobile;

use Illuminate\Database\Seeder;

/**
 * Master mobile-module seeder.
 *
 * Run with:
 *   php artisan db:seed --class=Mobile\\MobileDatabaseSeeder
 */
class MobileDatabaseSeeder extends Seeder
{
    public function run()
    {
        $this->call([
            MobileLabelsSeeder::class,
            AppScreenSettingsSeeder::class,
            AppUiSettingsSeeder::class,
            FeatureFlagsSeeder::class,
            UIControlsSeeder::class,
        ]);
    }
}
