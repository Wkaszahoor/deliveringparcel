<?php

namespace Database\Seeders;

use App\Models\WidgetInstance;
use Illuminate\Database\Seeder;

class WidgetSeeder extends Seeder
{
    public function run()
    {
        if (WidgetInstance::exists()) {
            return;
        }

        WidgetInstance::create([
            'type' => 'review_widget', 'title' => 'Home — customer reviews',
            'area' => 'home_bottom', 'scope' => 'global', 'scope_key' => null,
            'config' => ['title' => 'What our customers say', 'limit' => 6, 'sort' => 'rating_high', 'layout' => 'grid', 'verified' => 1, 'rating_min' => 4],
            'is_active' => true, 'sort' => 10, 'cache_minutes' => 10,
        ]);

        WidgetInstance::create([
            'type' => 'blade', 'title' => 'Home — CTA strip',
            'area' => 'home_bottom', 'scope' => 'global', 'scope_key' => null,
            'config' => ['view' => 'cta-strip', 'title' => 'Ready to ship?', 'text' => 'Book a delivery in under two minutes.', 'button' => 'Get started', 'url' => '/home2/services'],
            'is_active' => true, 'sort' => 20, 'cache_minutes' => 10,
        ]);

        WidgetInstance::create([
            'type' => 'blade', 'title' => 'Footer — stats row',
            'area' => 'footer', 'scope' => 'global', 'scope_key' => null,
            'config' => ['Parcels delivered' => '48k+', 'Countries' => '62', 'Avg. rating' => '4.8★', 'Support' => '24/7'],
            'is_active' => true, 'sort' => 10, 'cache_minutes' => 60,
        ]);
    }
}
