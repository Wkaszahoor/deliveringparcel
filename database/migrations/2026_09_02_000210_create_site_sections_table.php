<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Site Sections (2026-09-02) — top-level site groups managed via
 * the admin Section Manager (blog / shop / services / contact / pages).
 */
class CreateSiteSectionsTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('site_sections', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();    // 'blog','shop','services','contact','pages'
            $table->string('name');             // 'Blog'
            $table->string('icon')->nullable();
            $table->text('description')->nullable();
            $table->boolean('is_enabled')->default(true);
            $table->boolean('show_in_nav')->default(true);
            $table->integer('sort_order')->default(0);
            $table->string('index_route')->nullable(); // route name or URL for main section page
            $table->timestamps();
        });

        // ── Seed the 5 core sections ─────────────────────────────────
        DB::table('site_sections')->insert([
            [
                'key'          => 'blog',
                'name'         => 'Blog',
                'icon'         => 'fa-blog',
                'description'  => 'Blog posts and articles',
                'is_enabled'   => true,
                'show_in_nav'  => true,
                'sort_order'   => 1,
                'index_route'  => '/blog',
                'created_at'   => now(),
                'updated_at'   => now(),
            ],
            [
                'key'          => 'shop',
                'name'         => 'Shop',
                'icon'         => 'fa-shopping-cart',
                'description'  => 'Products and e-commerce',
                'is_enabled'   => true,
                'show_in_nav'  => true,
                'sort_order'   => 2,
                'index_route'  => '/shop',
                'created_at'   => now(),
                'updated_at'   => now(),
            ],
            [
                'key'          => 'services',
                'name'         => 'Services',
                'icon'         => 'fa-cogs',
                'description'  => 'Company services',
                'is_enabled'   => true,
                'show_in_nav'  => true,
                'sort_order'   => 3,
                'index_route'  => '/services',
                'created_at'   => now(),
                'updated_at'   => now(),
            ],
            [
                'key'          => 'contact',
                'name'         => 'Contact',
                'icon'         => 'fa-envelope',
                'description'  => 'Contact Us / Terms',
                'is_enabled'   => true,
                'show_in_nav'  => true,
                'sort_order'   => 4,
                'index_route'  => '/contact',
                'created_at'   => now(),
                'updated_at'   => now(),
            ],
            [
                'key'          => 'pages',
                'name'         => 'Pages',
                'icon'         => 'fa-file-alt',
                'description'  => 'Static / dynamic pages',
                'is_enabled'   => true,
                'show_in_nav'  => false,
                'sort_order'   => 5,
                'index_route'  => null,
                'created_at'   => now(),
                'updated_at'   => now(),
            ],
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('site_sections');
    }
}
