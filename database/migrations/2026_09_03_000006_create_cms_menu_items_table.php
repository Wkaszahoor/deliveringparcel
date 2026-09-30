<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\{Schema, DB};

/*
 * PROJECT ADAPTATION (deviation from upstream spec):
 * The legacy `nav_menu_items` table ALREADY exists in this project
 * (migration 2026_08_18_150001, legacy App\Models\NavMenuItem + NavRenderer
 * render the /home2 header from it, with live rows). Repurposing or
 * recreating that table would break the frozen legacy navigation.
 *
 * The CMS menu system therefore stores its items in `cms_menu_items`
 * (same column set as the spec's nav_menu_items), scoped by menu_id
 * to the new `nav_menus` table.
 */

if (!class_exists('CreateCmsMenuItemsTable')) {
    class CreateCmsMenuItemsTable extends Migration
    {
        public function up()
        {
            if (!Schema::hasTable('cms_menu_items')) {
                Schema::create('cms_menu_items', function (Blueprint $table) {
                    $table->id();
                    $table->unsignedBigInteger('menu_id');          // FK → nav_menus.id
                    $table->unsignedInteger('parent_id')->nullable()->index();
                    // NULL = top-level, non-null = dropdown child

                    $table->string('label');                        // "Blog" / "Our Services"
                    $table->string('item_type', 30)->default('custom');
                    // VALUES:
                    // custom       → custom URL
                    // cms_post     → links to a cms_posts record
                    // taxonomy     → links to a cms_taxonomies record
                    // section      → links to a section index (/blog /shop /services)
                    // route        → named Laravel route

                    $table->unsignedBigInteger('object_id')->nullable();
                    // cms_post.id OR cms_taxonomy.id depending on item_type

                    $table->string('url')->nullable();
                    // used when item_type = custom OR as override for any type

                    $table->string('target', 10)->default('_self'); // _self | _blank
                    $table->string('icon', 100)->nullable();        // fa-home
                    $table->string('css_class', 200)->nullable();
                    $table->boolean('is_mega_menu')->default(false);
                    $table->boolean('show_badge')->default(false);
                    $table->string('badge_text', 50)->nullable();   // "New" / "Hot"
                    $table->string('badge_color', 30)->nullable();  // bg-danger / bg-success
                    $table->integer('sort_order')->default(0);
                    $table->boolean('is_active')->default(true);
                    $table->timestamps();

                    $table->index(['menu_id', 'parent_id', 'sort_order']);
                    $table->foreign('menu_id')->references('id')->on('nav_menus')->onDelete('cascade');
                });
            }

            // Seed default navigation items (only when table is empty —
            // batch inserts need every row to carry the same column set)
            if (DB::table('cms_menu_items')->count() > 0) {
                return;
            }

            $headerMenu   = DB::table('nav_menus')->where('location', 'header_main')->value('id');
            $footerCol1   = DB::table('nav_menus')->where('location', 'footer_col1')->value('id');
            $footerBottom = DB::table('nav_menus')->where('location', 'footer_bottom')->value('id');

            $now = now();

            $row = function (array $overrides) use ($now) {
                return array_merge([
                    'parent_id'    => null,
                    'item_type'    => 'custom',
                    'object_id'    => null,
                    'url'          => null,
                    'target'       => '_self',
                    'icon'         => null,
                    'css_class'    => null,
                    'is_mega_menu' => false,
                    'show_badge'   => false,
                    'badge_text'   => null,
                    'badge_color'  => null,
                    'is_active'    => true,
                    'created_at'   => $now,
                    'updated_at'   => $now,
                ], $overrides);
            };

            // ── Header Main Menu ──
            DB::table('cms_menu_items')->insert([
                // Home
                $row(['menu_id' => $headerMenu, 'label' => 'Home', 'url' => '/',
                      'sort_order' => 1, 'icon' => 'fa-home']),

                // Blog
                $row(['menu_id' => $headerMenu, 'label' => 'Blog', 'item_type' => 'section',
                      'url' => '/blog', 'sort_order' => 2, 'icon' => 'fa-blog']),

                // Services (parent — will get children)
                $row(['menu_id' => $headerMenu, 'label' => 'Services', 'item_type' => 'section',
                      'url' => '/services', 'sort_order' => 3, 'icon' => 'fa-cogs']),

                // Shop
                $row(['menu_id' => $headerMenu, 'label' => 'Shop', 'item_type' => 'section',
                      'url' => '/shop', 'sort_order' => 4, 'icon' => 'fa-shopping-cart']),

                // Contact
                $row(['menu_id' => $headerMenu, 'label' => 'Contact', 'url' => '/contact',
                      'sort_order' => 5, 'icon' => 'fa-envelope']),
            ]);

            // Fetch Services item id to add children
            $servicesItemId = DB::table('cms_menu_items')
                ->where('menu_id', $headerMenu)
                ->where('label', 'Services')
                ->value('id');

            DB::table('cms_menu_items')->insert([
                // Sub-items under Services
                $row(['menu_id' => $headerMenu, 'parent_id' => $servicesItemId, 'label' => 'UK Delivery',
                      'url' => '/services/uk-delivery', 'sort_order' => 1, 'icon' => 'fa-truck']),
                $row(['menu_id' => $headerMenu, 'parent_id' => $servicesItemId, 'label' => 'International Shipping',
                      'url' => '/services/international', 'sort_order' => 2, 'icon' => 'fa-globe']),
                $row(['menu_id' => $headerMenu, 'parent_id' => $servicesItemId, 'label' => 'Get a Quote',
                      'url' => '/get-quote', 'sort_order' => 3, 'icon' => 'fa-calculator',
                      'show_badge' => true, 'badge_text' => 'Free', 'badge_color' => 'bg-success']),
            ]);

            // ── Footer Column 1 ──
            DB::table('cms_menu_items')->insert([
                $row(['menu_id' => $footerCol1, 'label' => 'About Us', 'url' => '/about', 'sort_order' => 1]),
                $row(['menu_id' => $footerCol1, 'label' => 'Careers', 'url' => '/page/careers', 'sort_order' => 2]),
                $row(['menu_id' => $footerCol1, 'label' => 'Blog', 'item_type' => 'section', 'url' => '/blog', 'sort_order' => 3]),
            ]);

            // ── Footer Bottom ──
            // (Real pages, not the placeholder /page/* stubs the spec
            // shipped with — deliveringparcel.com has no separate cookie
            // policy, so that slot points at Refund Policy instead.)
            DB::table('cms_menu_items')->insert([
                $row(['menu_id' => $footerBottom, 'label' => 'Privacy Policy', 'url' => '/privacy-policy', 'sort_order' => 1]),
                $row(['menu_id' => $footerBottom, 'label' => 'Terms of Business', 'url' => '/terms-and-conditions', 'sort_order' => 2]),
                $row(['menu_id' => $footerBottom, 'label' => 'Refund Policy', 'url' => '/refund-policy', 'sort_order' => 3]),
            ]);
        }

        public function down()
        {
            Schema::dropIfExists('cms_menu_items');
        }
    }
} // end class_exists guard

return new CreateCmsMenuItemsTable();
