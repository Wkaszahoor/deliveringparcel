<?php

namespace Database\Seeders\Mobile;

use App\Mobile\Models\MobileFeatureFlag;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Schema;

class FeatureFlagsSeeder extends Seeder
{
    public function run()
    {
        if (!Schema::hasTable('mobile_feature_flags')) {
            return;
        }

        $flags = [
            // [key, name, enabled, roles]
            ['order_management', 'Order Management',   true,  ['admin', 'client']],
            ['live_chat',        'Live Chat',          true,  ['admin', 'client']],
            ['push_notifs',      'Push Notifications', true,  ['admin', 'client']],
            ['file_upload',      'File Upload',        false, ['admin']],
            ['order_tracking',   'Order Tracking',     false, ['admin', 'client']],
            ['rating_system',    'Rating System',      true,  ['client']],
        ];

        foreach ($flags as [$key, $name, $enabled, $roles]) {
            MobileFeatureFlag::updateOrCreate(
                ['feature_key' => $key],
                ['feature_name' => $name, 'is_enabled' => $enabled, 'allowed_roles' => $roles]
            );
        }
    }
}
