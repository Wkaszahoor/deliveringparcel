<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\{Schema, DB};

if (!class_exists('CreateCmsTaxonomiesTable')) {
    class CreateCmsTaxonomiesTable extends Migration
    {
        public function up()
        {
            Schema::create('cms_taxonomies', function (Blueprint $table) {
                $table->id();
                $table->string('taxonomy', 50)->index();
                // VALUES: category | tag | service_area
                $table->string('post_type', 50)->index();
                // which post_type this taxonomy serves
                $table->string('name');
                $table->string('slug')->index();
                $table->text('description')->nullable();
                $table->string('featured_image')->nullable();
                $table->unsignedInteger('parent_id')->nullable();
                $table->integer('sort_order')->default(0);
                $table->unsignedInteger('post_count')->default(0);
                $table->json('meta')->nullable();
                $table->timestamps();

                $table->unique(['taxonomy', 'post_type', 'slug'], 'tax_posttype_slug_unique');
            });

            // Seed default taxonomies
            DB::table('cms_taxonomies')->insert([
                // Blog categories
                ['taxonomy' => 'category', 'post_type' => 'blog_post', 'name' => 'General',
                 'slug' => 'general', 'description' => 'General blog posts',
                 'parent_id' => null, 'sort_order' => 1, 'post_count' => 0,
                 'meta' => null, 'created_at' => now(), 'updated_at' => now()],
                ['taxonomy' => 'category', 'post_type' => 'blog_post', 'name' => 'Company News',
                 'slug' => 'company-news', 'description' => 'Latest company updates',
                 'parent_id' => null, 'sort_order' => 2, 'post_count' => 0,
                 'meta' => null, 'created_at' => now(), 'updated_at' => now()],
                ['taxonomy' => 'category', 'post_type' => 'blog_post', 'name' => 'Tips & Guides',
                 'slug' => 'tips-guides', 'description' => 'How-to articles',
                 'parent_id' => null, 'sort_order' => 3, 'post_count' => 0,
                 'meta' => null, 'created_at' => now(), 'updated_at' => now()],

                // Service areas
                ['taxonomy' => 'service_area', 'post_type' => 'service', 'name' => 'UK Delivery',
                 'slug' => 'uk-delivery', 'description' => 'UK domestic delivery services',
                 'parent_id' => null, 'sort_order' => 1, 'post_count' => 0,
                 'meta' => null, 'created_at' => now(), 'updated_at' => now()],
                ['taxonomy' => 'service_area', 'post_type' => 'service', 'name' => 'International',
                 'slug' => 'international', 'description' => 'International shipping',
                 'parent_id' => null, 'sort_order' => 2, 'post_count' => 0,
                 'meta' => null, 'created_at' => now(), 'updated_at' => now()],

                // Product categories
                ['taxonomy' => 'category', 'post_type' => 'product', 'name' => 'Packaging',
                 'slug' => 'packaging', 'description' => 'Packaging supplies',
                 'parent_id' => null, 'sort_order' => 1, 'post_count' => 0,
                 'meta' => json_encode(['display_type' => 'grid']), 'created_at' => now(), 'updated_at' => now()],
                ['taxonomy' => 'category', 'post_type' => 'product', 'name' => 'Courier Services',
                 'slug' => 'courier-services', 'description' => 'Courier service products',
                 'parent_id' => null, 'sort_order' => 2, 'post_count' => 0,
                 'meta' => json_encode(['display_type' => 'grid']), 'created_at' => now(), 'updated_at' => now()],
            ]);
        }

        public function down()
        {
            Schema::dropIfExists('cms_taxonomies');
        }
    }
} // end class_exists guard

return new CreateCmsTaxonomiesTable();
