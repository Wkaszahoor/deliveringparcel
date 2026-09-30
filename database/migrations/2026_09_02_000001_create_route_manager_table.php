<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CreateRouteManagerTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('route_manager_settings', function (Blueprint $table) {
            $table->id();
            $table->string('route_key', 100)->unique();   // machine key e.g. 'client_panel'
            $table->string('label', 150);                 // human label: "Client Panel"
            $table->string('group', 80);                  // 'navigation'|'page'|'section'
            $table->enum('current_version', ['legacy', 'new'])->default('legacy');
            $table->string('legacy_url', 500)->nullable();// the old Blade route/URL
            $table->string('new_url', 500)->nullable();   // the new design route/URL
            $table->boolean('show_in_header')->default(false);
            $table->boolean('show_in_footer')->default(false);
            $table->boolean('show_in_main_nav')->default(false);
            $table->boolean('is_locked')->default(false); // admin must type confirmation to switch
            $table->text('notes')->nullable();
            $table->unsignedBigInteger('created_by')->nullable(); // FK users.id
            $table->unsignedBigInteger('updated_by')->nullable(); // FK users.id
            $table->timestamps();
        });

        DB::table('route_manager_settings')->insert([
            // ── CLIENT PANEL ──────────────────────────────────────────────
            [
                'route_key' => 'client_panel',
                'label' => 'Client Dashboard / Panel',
                'group' => 'page',
                'current_version' => 'legacy',
                'legacy_url' => '/home2/dashboard',
                'new_url' => '/client/dashboard',
                'show_in_header' => true,
                'show_in_footer' => false,
                'show_in_main_nav' => true,
                'is_locked' => false,
                'notes' => 'Keep on legacy until new client panel is fully tested',
            ],

            // ── BLOG ──────────────────────────────────────────────────────
            [
                'route_key' => 'blog',
                'label' => 'Blog',
                'group' => 'navigation',
                'current_version' => 'new',
                'legacy_url' => '/blog',
                'new_url' => '/blog',
                'show_in_header' => true,
                'show_in_footer' => true,
                'show_in_main_nav' => true,
                'is_locked' => false,
                'notes' => 'New BlogController owns /blog outright (2026-09-02) — legacy and new URLs are identical',
            ],

            // ── SERVICES PAGE ─────────────────────────────────────────────
            [
                'route_key' => 'services',
                'label' => 'Services Page',
                'group' => 'page',
                'current_version' => 'legacy',
                'legacy_url' => '/services',
                'new_url' => '/new/services',
                'show_in_header' => true,
                'show_in_footer' => true,
                'show_in_main_nav' => true,
                'is_locked' => false,
                'notes' => 'Services page — legacy until redesign approved',
            ],

            // ── TESTIMONIALS ──────────────────────────────────────────────
            [
                'route_key' => 'testimonials',
                'label' => 'Testimonials / Reviews',
                'group' => 'section',
                'current_version' => 'legacy',
                'legacy_url' => '/testimonials',
                'new_url' => '/new/testimonials',
                'show_in_header' => false,
                'show_in_footer' => true,
                'show_in_main_nav' => false,
                'is_locked' => false,
                'notes' => 'Testimonials section — footer link target',
            ],

            // ── MAIN HOMEPAGE ─────────────────────────────────────────────
            [
                'route_key' => 'homepage',
                'label' => 'Main Homepage',
                'group' => 'page',
                'current_version' => 'legacy',
                'legacy_url' => '/',
                'new_url' => '/new/home',
                'show_in_header' => false,
                'show_in_footer' => false,
                'show_in_main_nav' => false,
                'is_locked' => true,
                'notes' => 'Homepage — locked, requires confirmation to switch',
            ],

            // ── REGISTER / SIGNUP ─────────────────────────────────────────
            [
                'route_key' => 'register',
                'label' => 'Register / Sign Up',
                'group' => 'navigation',
                'current_version' => 'legacy',
                'legacy_url' => '/register',
                'new_url' => '/new/register',
                'show_in_header' => true,
                'show_in_footer' => false,
                'show_in_main_nav' => true,
                'is_locked' => false,
                'notes' => 'Register link in header',
            ],

            // ── LOGIN ─────────────────────────────────────────────────────
            [
                'route_key' => 'login',
                'label' => 'Login',
                'group' => 'navigation',
                'current_version' => 'legacy',
                'legacy_url' => '/login',
                'new_url' => '/new/login',
                'show_in_header' => true,
                'show_in_footer' => false,
                'show_in_main_nav' => true,
                'is_locked' => false,
                'notes' => 'Login link in header',
            ],

            // ── GET A QUOTE / ORDER ───────────────────────────────────────
            [
                'route_key' => 'get_quote',
                'label' => 'Get a Quote / Place Order',
                'group' => 'navigation',
                'current_version' => 'legacy',
                'legacy_url' => '/get-quote',
                'new_url' => '/new/order',
                'show_in_header' => true,
                'show_in_footer' => false,
                'show_in_main_nav' => true,
                'is_locked' => false,
                'notes' => 'Primary CTA button in header',
            ],

            // ── TRACKING PAGE ─────────────────────────────────────────────
            [
                'route_key' => 'tracking',
                'label' => 'Order Tracking Page',
                'group' => 'page',
                'current_version' => 'legacy',
                'legacy_url' => '/tracking',
                'new_url' => '/new/tracking',
                'show_in_header' => false,
                'show_in_footer' => true,
                'show_in_main_nav' => false,
                'is_locked' => false,
                'notes' => 'Public tracking lookup page',
            ],

            // ── CONTACT ───────────────────────────────────────────────────
            [
                'route_key' => 'contact',
                'label' => 'Contact Page',
                'group' => 'page',
                'current_version' => 'legacy',
                'legacy_url' => '/contact',
                'new_url' => '/new/contact',
                'show_in_header' => false,
                'show_in_footer' => true,
                'show_in_main_nav' => false,
                'is_locked' => false,
                'notes' => 'Contact page link in footer',
            ],

            // ── ABOUT ─────────────────────────────────────────────────────
            [
                'route_key' => 'about',
                'label' => 'About Page',
                'group' => 'page',
                'current_version' => 'legacy',
                'legacy_url' => '/about',
                'new_url' => '/new/about',
                'show_in_header' => false,
                'show_in_footer' => true,
                'show_in_main_nav' => false,
                'is_locked' => false,
                'notes' => 'About us page',
            ],

            // ── MOBILE APP API BASE ───────────────────────────────────────
            [
                'route_key' => 'mobile_api',
                'label' => 'Mobile App API Version',
                'group' => 'page',
                'current_version' => 'legacy',
                'legacy_url' => '/api',
                'new_url' => '/api/v2',
                'show_in_header' => false,
                'show_in_footer' => false,
                'show_in_main_nav' => false,
                'is_locked' => true,
                'notes' => 'Mobile API base URL — locked',
            ],
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('route_manager_settings');
    }
}
