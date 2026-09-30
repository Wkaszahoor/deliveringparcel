<?php

namespace Database\Seeders;

use App\Models\HeroSlide;
use Illuminate\Database\Seeder;

class HeroSlideSeeder extends Seeder
{
    public function run()
    {
        $slides = [
            [
                'title'     => 'Ship Anything, Anywhere',
                'subtitle'  => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit — fast worldwide parcel delivery.',
                'image'     => 'hero-slide-placeholder-1.jpg',
                'btn_text'  => 'Get a Quote',
                'btn_link'  => '/freequote',
                'position'  => 1,
                'is_active' => true,
                'options'   => ['text_align' => 'left', 'badge_text' => 'NEW', 'overlay_opacity' => 0.4],
            ],
            [
                'title'     => 'Freight Solutions for Every Business',
                'subtitle'  => 'Praesent sapien massa, convallis a pellentesque nec — air, sea and road freight.',
                'image'     => 'hero-slide-placeholder-2.jpg',
                'btn_text'  => 'Our Services',
                'btn_link'  => '/services',
                'position'  => 2,
                'is_active' => true,
                'options'   => ['text_align' => 'center', 'theme' => 'dark', 'animation' => 'slide'],
            ],
        ];

        foreach ($slides as $data) {
            HeroSlide::firstOrCreate(['title' => $data['title']], $data);
        }
    }
}
