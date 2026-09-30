<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

if (!class_exists('CreateCmsPostsTable')) {
    class CreateCmsPostsTable extends Migration
    {
        public function up()
        {
            Schema::create('cms_posts', function (Blueprint $table) {
                $table->id();

                // --- Identity ---
                $table->string('post_type', 50)->default('page')->index();
                // VALUES: page | blog_post | product | service | portfolio

                $table->string('title');
                $table->string('slug')->unique();
                $table->string('status', 20)->default('draft')->index();
                // VALUES: draft | published | scheduled | private | trashed

                // --- Content ---
                $table->longText('content')->nullable();
                $table->text('excerpt')->nullable();
                $table->string('featured_image')->nullable();
                // stored as relative path: uploads/cms/YYYY/MM/filename.jpg

                // --- Hierarchy (pages: parent/child, services: parent=service child=sub-service) ---
                $table->unsignedInteger('parent_id')->nullable()->index();
                $table->integer('menu_order')->default(0);

                // --- Publishing ---
                $table->timestamp('published_at')->nullable();
                $table->unsignedInteger('author_id')->nullable()->index();

                // --- SEO ---
                $table->string('meta_title')->nullable();
                $table->text('meta_description')->nullable();
                $table->string('meta_keywords')->nullable();
                $table->string('og_image')->nullable();
                $table->enum('robots', ['index,follow', 'noindex,follow', 'index,nofollow', 'noindex,nofollow'])
                      ->default('index,follow');

                // --- Display / Layout ---
                $table->string('template', 100)->nullable();
                $table->string('layout', 100)->default('home2.layouts.app');
                $table->boolean('show_in_header')->default(false);
                $table->boolean('show_in_footer')->default(false);
                $table->boolean('show_in_main_nav')->default(false);

                // --- Post-type-specific JSON blob ---
                $table->json('meta')->nullable();

                // --- Tracking ---
                $table->unsignedInteger('view_count')->default(0);
                $table->boolean('is_locked')->default(false);

                $table->timestamps();
                $table->softDeletes();

                $table->index(['post_type', 'status', 'published_at']);
                $table->index(['parent_id', 'menu_order']);
            });
        }

        public function down()
        {
            Schema::dropIfExists('cms_posts');
        }
    }
} // end class_exists guard

return new CreateCmsPostsTable();
