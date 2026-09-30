<?php

namespace Database\Seeders\Mobile;

use App\Mobile\Models\AppScreenSetting;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Schema;

class AppScreenSettingsSeeder extends Seeder
{
    public function run()
    {
        if (!Schema::hasTable('app_screen_settings')) {
            return;
        }

        $screens = [
            // [key, name, parent, order, visible, config]
            ['home',           'Home',           null,       1, true,  null],
            ['orders',         'Orders',         null,       2, true,  [
                'visible_elements' => [
                    'order_number'   => true,
                    'order_status'   => true,
                    'order_price'    => true,
                    'order_notes'    => false,
                    'action_buttons' => true,
                ],
                'visible_buttons' => [
                    'view_detail'  => true,
                    'track_order'  => true,
                    'cancel_order' => false,
                    'reorder'      => false,
                ],
            ]],
            ['order_detail',   'Order Detail',   'orders',   1, true,  null],
            ['order_tracking', 'Order Tracking', 'orders',   2, false, null],
            ['messages',       'Messages',       null,       3, true,  [
                // Mirror of the canonical colors in app_ui_settings
                // (group message_colors) — the API reads app_ui_settings.
                'admin'  => ['bubble' => '#1565C0', 'text' => '#FFFFFF', 'position' => 'right'],
                'client' => ['bubble' => '#E8F5E9', 'text' => '#212121', 'position' => 'left'],
                'system' => ['bubble' => '#FFF3E0', 'text' => '#E65100', 'position' => 'center'],
            ]],
            ['chat_room',      'Chat Room',      'messages', 1, true,  null],
            ['notifications',  'Notifications',  null,       4, true,  null],
            ['profile',        'Profile',        null,       5, true,  null],
            ['edit_profile',   'Edit Profile',   'profile',  1, true,  null],
            ['settings',       'Settings',       null,       6, false, null],
        ];

        foreach ($screens as [$key, $name, $parent, $order, $visible, $config]) {
            AppScreenSetting::updateOrCreate(
                ['screen_key' => $key],
                [
                    'screen_name'   => $name,
                    'parent_screen' => $parent,
                    'is_visible'    => $visible,
                    'is_enabled'    => true,
                    'visible_to'    => ['admin', 'client'],
                    'screen_order'  => $order,
                    'config'        => $config,
                ]
            );
        }
    }
}
