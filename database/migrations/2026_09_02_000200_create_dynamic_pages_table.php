<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Dynamic Pages (2026-09-02) — admin-managed public pages
 * (Careers, FAQ, Privacy Policy, Sitemap-html, ...).
 *
 * Each row is publicly served via /page/{slug}
 * (DynamicPageController@show) and auto-synced into the
 * route_manager_settings table by the admin controller.
 */
class CreateDynamicPagesTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('dynamic_pages', function (Blueprint $table) {
            $table->id();
            $table->string('title');                  // "Careers"
            $table->string('slug')->unique();         // "careers"
            $table->string('route_path')->unique();   // "/careers"
            $table->string('nav_label')->nullable();  // label shown in nav
            $table->string('section')
                  ->default('pages')
                  ->comment('blog|shop|services|pages|careers|custom');
            $table->string('icon')->nullable();       // fa-briefcase
            $table->text('meta_title')->nullable();
            $table->text('meta_description')->nullable();
            $table->text('meta_keywords')->nullable();
            $table->longText('content')->nullable();  // rich HTML content
            $table->string('layout')->default('home2.layouts.app');
            $table->boolean('show_in_header')->default(false);
            $table->boolean('show_in_footer')->default(false);
            $table->boolean('show_in_main_nav')->default(true);
            $table->boolean('is_active')->default(true);
            $table->boolean('is_locked')->default(false);
            $table->integer('sort_order')->default(0);
            $table->string('controller_override')->nullable();
            // e.g. "App\Http\Controllers\CareersController" if custom
            $table->timestamps();
            $table->softDeletes();
        });

        // ── Seed 4 example rows ──────────────────────────────────────
        DB::table('dynamic_pages')->insert([
            [
                'title'            => 'Careers',
                'slug'             => 'careers',
                'route_path'       => '/careers',
                'nav_label'        => 'Careers',
                'section'          => 'pages',
                'icon'             => 'fa-briefcase',
                'meta_title'       => 'Careers | Delivering Parcel',
                'meta_description' => 'Join our team at Delivering Parcel.',
                'content'          => '<h2>Work With Us</h2><p>We are hiring! Check back soon for open positions.</p>',
                'layout'           => 'home2.layouts.app',
                'show_in_header'   => true,
                'show_in_footer'   => true,
                'show_in_main_nav' => true,
                'is_active'        => true,
                'sort_order'       => 10,
                'created_at'       => now(),
                'updated_at'       => now(),
            ],
            [
                'title'            => 'FAQ',
                'slug'             => 'faq',
                'route_path'       => '/faq',
                'nav_label'        => 'FAQ',
                'section'          => 'pages',
                'icon'             => 'fa-question-circle',
                'meta_title'       => 'FAQ | Delivering Parcel',
                'meta_description' => null,
                'content'          => '<h2>Frequently Asked Questions</h2><p>Content coming soon.</p>',
                'layout'           => 'home2.layouts.app',
                'show_in_header'   => false,
                'show_in_footer'   => true,
                'show_in_main_nav' => true,
                'is_active'        => true,
                'sort_order'       => 20,
                'created_at'       => now(),
                'updated_at'       => now(),
            ],
            [
                'title'            => 'Privacy Policy',
                'slug'             => 'privacy-policy',
                'route_path'       => '/privacy-policy',
                'nav_label'        => 'Privacy Policy',
                'section'          => 'pages',
                'icon'             => 'fa-shield-alt',
                'meta_title'       => 'Privacy Policy | Delivering Parcel',
                'meta_description' => null,
                'content'          => '<h2>Privacy Policy</h2><p>Your privacy matters to us.</p>',
                'layout'           => 'home2.layouts.app',
                'show_in_header'   => false,
                'show_in_footer'   => true,
                'show_in_main_nav' => false,
                'is_active'        => true,
                'sort_order'       => 30,
                'created_at'       => now(),
                'updated_at'       => now(),
            ],
            [
                'title'            => 'Sitemap',
                'slug'             => 'sitemap-html',
                'route_path'       => '/sitemap-html',
                'nav_label'        => 'Sitemap',
                'section'          => 'pages',
                'icon'             => 'fa-sitemap',
                'meta_title'       => 'Sitemap | Delivering Parcel',
                'meta_description' => null,
                'content'          => '<h2>Sitemap</h2>',
                'layout'           => 'home2.layouts.app',
                'show_in_header'   => false,
                'show_in_footer'   => true,
                'show_in_main_nav' => false,
                'is_active'        => false,
                'sort_order'       => 40,
                'created_at'       => now(),
                'updated_at'       => now(),
            ],
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('dynamic_pages');
    }
}
