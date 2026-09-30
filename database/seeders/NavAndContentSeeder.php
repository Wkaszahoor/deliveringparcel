<?php

namespace Database\Seeders;

use App\Models\NavMenuItem;
use App\Services\NavRenderer;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * FE agent — seeds:
 *  - the default /home2 header menu (FE-004/FE-005)
 *  - fallback values for site-wide content settings (FE-003/CT-001)
 *
 * Idempotent: existing rows/values are never overwritten.
 */
class NavAndContentSeeder extends Seeder
{
    public function run()
    {
        $this->seedMenu();
        $this->seedSettings();
    }

    protected function seedMenu()
    {
        // [title, label, route_name, url, icon, visibility]
        $items = [
            ['Home',        null,        'home2.index',    null,        null, 'everyone'],
            ['Services',    null,        'home2.services', null,        null, 'everyone'],
            ['Blog',        null,        'home2.blog',     null,        null, 'everyone'],
            ['Track Order', 'Track Order', 'home2.track',  null,        null, 'everyone'],
            ['Contact',     null,        'home2.contact',  null,        null, 'everyone'],
            ['Login',       'Login',     null,             'login',     null, 'guest'],
            ['My Account',  'My Account', null,            'home2/dashboard', null, 'auth'],
        ];

        foreach ($items as $i => $data) {
            NavMenuItem::firstOrCreate(
                ['title' => $data[0], 'parent_id' => null],
                [
                    'label'      => $data[1],
                    'url'        => $data[3],
                    'route_name' => $data[2],
                    'icon_class' => $data[4],
                    'sort'       => $i,
                    'is_active'  => true,
                    'new_tab'    => false,
                    'visibility' => $data[5],
                ]
            );
        }

        NavRenderer::flushCache();
    }

    protected function seedSettings()
    {
        // business_* keys are already managed by the admin Settings module —
        // only fill them when the row is missing entirely.
        $business = [
            'business_support_email' => ['service@deliveringparcel.com', 'business', 'string'],
            'business_support_phone' => ['', 'business', 'string'],
            'business_address'       => ['', 'business', 'text'],
            'business_hours'         => ['Mon–Fri, 9:00–18:00', 'business', 'string'],
        ];

        foreach ($business as $key => $def) {
            if (!DB::table('settings')->where('key', $key)->exists()) {
                \App\Models\Setting::set($key, $def[0], $def[1], $def[2]);
            }
        }

        // social_* keys consumed by the home2 footer (empty = hidden).
        $socials = [
            'social_facebook'  => 'https://www.facebook.com/deliveringparcel',
            'social_instagram' => 'https://www.instagram.com/delivering_parcel',
            'social_youtube'   => 'https://www.youtube.com/@deliveringparcel-ur8wc',
            'social_twitter'   => '',
            'social_linkedin'  => '',
            'social_whatsapp'  => '',
        ];

        foreach ($socials as $key => $value) {
            if (!DB::table('settings')->where('key', $key)->exists()) {
                \App\Models\Setting::set($key, $value, 'social', 'string');
            }
        }
    }
}
