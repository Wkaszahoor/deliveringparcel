<?php

namespace Database\Seeders\Mobile;

use App\Mobile\Models\AppUiSetting;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Schema;

class AppUiSettingsSeeder extends Seeder
{
    public function run()
    {
        if (!Schema::hasTable('app_ui_settings')) {
            return;
        }

        $settings = [
            // group: message_colors — WhatsApp-style defaults: own green
            // right / others white left (admin can override in the panel).
            'admin_bubble_color'  => ['#dcf8c6', 'message_colors'],
            'admin_text_color'    => ['#212121', 'message_colors'],
            'admin_position'      => ['right',   'message_colors'],
            'client_bubble_color' => ['#ffffff', 'message_colors'],
            'client_text_color'   => ['#212121', 'message_colors'],
            'client_position'     => ['left',    'message_colors'],
            'system_bubble_color' => ['#FFF3E0', 'message_colors'],
            'system_text_color'   => ['#E65100', 'message_colors'],
            'system_position'     => ['center',  'message_colors'],

            // group: app_colors
            'primary_color'   => ['#2196F3', 'app_colors'],
            'secondary_color' => ['#FF9800', 'app_colors'],
            'background_color'=> ['#FFFFFF', 'app_colors'],

            // group: layout — keeps content clear of the bottom tab bar
            'screen_bottom_padding' => ['90', 'layout'],

            // group: typography
            'font_size_base'    => ['14', 'typography'],
            'font_size_header'  => ['18', 'typography'],
            'font_size_message' => ['14', 'typography'],

            // group: system (maintenance / force-update — Features page)
            'maintenance_active'      => ['0', 'system'],
            'maintenance_message'     => ['We are performing scheduled maintenance. Please check back soon.', 'system'],
            'maintenance_expected_back' => ['', 'system'],
            'force_update_required'   => ['0', 'system'],
            'force_update_min_ios'    => ['1.0.0', 'system'],
            'force_update_min_android'=> ['1.0.0', 'system'],
            'force_update_message'    => ['A new version is available. Please update the app to continue.', 'system'],
        ];

        foreach ($settings as $key => [$value, $group]) {
            AppUiSetting::updateOrCreate(
                ['setting_key' => $key],
                ['setting_value' => $value, 'setting_group' => $group]
            );
        }
    }
}
