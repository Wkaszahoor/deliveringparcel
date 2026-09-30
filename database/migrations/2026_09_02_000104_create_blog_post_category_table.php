<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Blog Import System (2026-09-02) — blog_posts ↔ blog_categories pivot.
 */
class CreateBlogPostCategoryTable extends Migration
{
    public function up()
    {
        Schema::create('blog_post_category', function (Blueprint $table) {
            $table->unsignedBigInteger('post_id');
            $table->unsignedBigInteger('category_id');
            $table->primary(['post_id', 'category_id']);

            $table->foreign('post_id')->references('id')->on('blog_posts')->cascadeOnDelete();
            $table->foreign('category_id')->references('id')->on('blog_categories')->cascadeOnDelete();
        });
    }

    public function down()
    {
        Schema::dropIfExists('blog_post_category');
    }
}
