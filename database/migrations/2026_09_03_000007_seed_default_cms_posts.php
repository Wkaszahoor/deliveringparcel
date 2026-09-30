<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

if (!class_exists('SeedDefaultCmsPosts')) {
    class SeedDefaultCmsPosts extends Migration
    {
        public function up()
        {
            // Seed only if table empty to prevent duplicates
            if (DB::table('cms_posts')->count() === 0) {
                DB::table('cms_posts')->insert([
                    // ── Static Pages ──
                    ['post_type' => 'page', 'title' => 'Contact Us', 'slug' => 'contact',
                     'status' => 'published', 'content' => '<h2>Contact Us</h2><p>Get in touch with our team.</p>',
                     'excerpt' => 'Get in touch with our team.',
                     'meta_title' => 'Contact Us | Delivering Parcel',
                     'meta_description' => 'Contact the Delivering Parcel team.',
                     'layout' => 'home2.layouts.app', 'show_in_main_nav' => true,
                     'show_in_footer' => true, 'menu_order' => 1,
                     'meta' => json_encode(['show_title' => true, 'show_breadcrumb' => true]),
                     'published_at' => now(), 'created_at' => now(), 'updated_at' => now()],

                    ['post_type' => 'page', 'title' => 'Terms & Conditions', 'slug' => 'terms',
                     'status' => 'published',
                     'content' => '<h2>Terms &amp; Conditions</h2><p>Please read these terms carefully.</p>',
                     'excerpt' => 'Our terms and conditions of service.',
                     'meta_title' => 'Terms & Conditions | Delivering Parcel',
                     'meta_description' => 'Terms and conditions for Delivering Parcel services.',
                     'layout' => 'home2.layouts.app', 'show_in_main_nav' => false,
                     'show_in_footer' => true, 'menu_order' => 2,
                     'meta' => json_encode(['show_title' => true, 'show_breadcrumb' => true]),
                     'published_at' => now(), 'created_at' => now(), 'updated_at' => now()],

                    ['post_type' => 'page', 'title' => 'Privacy Policy', 'slug' => 'privacy-policy',
                     'status' => 'published',
                     'content' => '<h2>Privacy Policy</h2><p>Your privacy is important to us.</p>',
                     'excerpt' => 'How we collect and use your data.',
                     'meta_title' => 'Privacy Policy | Delivering Parcel',
                     'meta_description' => 'Privacy policy for Delivering Parcel.',
                     'layout' => 'home2.layouts.app', 'show_in_main_nav' => false,
                     'show_in_footer' => true, 'menu_order' => 3,
                     'meta' => json_encode(['show_title' => true, 'show_breadcrumb' => true]),
                     'published_at' => now(), 'created_at' => now(), 'updated_at' => now()],

                    ['post_type' => 'page', 'title' => 'Careers', 'slug' => 'careers',
                     'status' => 'published',
                     'content' => '<h2>Work With Us</h2><p>We are hiring! Check back soon for open positions.</p>',
                     'excerpt' => 'Join our growing team at Delivering Parcel.',
                     'meta_title' => 'Careers | Delivering Parcel',
                     'meta_description' => 'Career opportunities at Delivering Parcel.',
                     'layout' => 'home2.layouts.app', 'show_in_main_nav' => true,
                     'show_in_footer' => true, 'menu_order' => 4,
                     'meta' => json_encode(['show_title' => true, 'show_breadcrumb' => true]),
                     'published_at' => now(), 'created_at' => now(), 'updated_at' => now()],

                    ['post_type' => 'page', 'title' => 'FAQ', 'slug' => 'faq',
                     'status' => 'published',
                     'content' => '<h2>Frequently Asked Questions</h2><p>Answers to common questions.</p>',
                     'excerpt' => 'Common questions about our services.',
                     'meta_title' => 'FAQ | Delivering Parcel',
                     'meta_description' => 'Frequently asked questions about Delivering Parcel.',
                     'layout' => 'home2.layouts.app', 'show_in_main_nav' => false,
                     'show_in_footer' => true, 'menu_order' => 5,
                     'meta' => json_encode(['show_title' => true, 'show_breadcrumb' => true]),
                     'published_at' => now(), 'created_at' => now(), 'updated_at' => now()],

                    // ── Services ──
                    ['post_type' => 'service', 'title' => 'UK Domestic Delivery', 'slug' => 'uk-delivery',
                     'status' => 'published',
                     'content' => '<h2>UK Domestic Delivery</h2><p>Fast, reliable UK parcel delivery.</p>',
                     'excerpt' => 'Fast, reliable UK parcel delivery for businesses and individuals.',
                     'meta_title' => 'UK Domestic Delivery | Delivering Parcel',
                     'layout' => 'home2.layouts.app', 'show_in_main_nav' => true,
                     'show_in_footer' => true, 'menu_order' => 1, 'parent_id' => null,
                     'meta' => json_encode(['icon' => 'fa-truck', 'cta_label' => 'Get Quote',
                                            'cta_url' => '/get-quote', 'is_featured' => true]),
                     'published_at' => now(), 'created_at' => now(), 'updated_at' => now()],

                    ['post_type' => 'service', 'title' => 'International Shipping', 'slug' => 'international-shipping',
                     'status' => 'published',
                     'content' => '<h2>International Shipping</h2><p>Ship parcels worldwide with tracking.</p>',
                     'excerpt' => 'Ship parcels worldwide with real-time tracking.',
                     'meta_title' => 'International Shipping | Delivering Parcel',
                     'layout' => 'home2.layouts.app', 'show_in_main_nav' => true,
                     'show_in_footer' => true, 'menu_order' => 2, 'parent_id' => null,
                     'meta' => json_encode(['icon' => 'fa-globe', 'cta_label' => 'Get Quote',
                                            'cta_url' => '/get-quote', 'is_featured' => true]),
                     'published_at' => now(), 'created_at' => now(), 'updated_at' => now()],

                    ['post_type' => 'service', 'title' => 'Same Day Delivery', 'slug' => 'same-day-delivery',
                     'status' => 'published',
                     'content' => '<h2>Same Day Delivery</h2><p>Urgent delivery within hours.</p>',
                     'excerpt' => 'Urgent parcel delivery within hours across the UK.',
                     'meta_title' => 'Same Day Delivery | Delivering Parcel',
                     'layout' => 'home2.layouts.app', 'show_in_main_nav' => false,
                     'show_in_footer' => false, 'menu_order' => 1, 'parent_id' => null,
                     'meta' => json_encode(['icon' => 'fa-bolt', 'cta_label' => 'Book Now',
                                            'cta_url' => '/get-quote', 'is_featured' => false]),
                     'published_at' => now(), 'created_at' => now(), 'updated_at' => now()],
                ]);

                // Set Same Day as child of UK Delivery
                $ukDeliveryId = DB::table('cms_posts')->where('slug', 'uk-delivery')->value('id');
                DB::table('cms_posts')->where('slug', 'same-day-delivery')
                    ->update(['parent_id' => $ukDeliveryId]);
            }
        }

        public function down()
        {
            // Only remove the seeded rows if they are still exactly the seeds
            $slugs = ['contact', 'terms', 'privacy-policy', 'careers', 'faq',
                      'uk-delivery', 'international-shipping', 'same-day-delivery'];
            DB::table('cms_posts')->whereIn('slug', $slugs)
                ->whereNull('featured_image')
                ->delete();
        }
    }
} // end class_exists guard

return new SeedDefaultCmsPosts();
