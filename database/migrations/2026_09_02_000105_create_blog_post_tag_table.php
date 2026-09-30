<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Blog Import System (2026-09-02) — blog_posts ↔ blog_tags pivot.
 */
class CreateBlogPostTagTable extends Migration
{
    public function up()
    {
        Schema::create('blog_post_tag', function (Blueprint $table) {
            $table->unsignedBigInteger('post_id');
            $table->unsignedBigInteger('tag_id');
            $table->primary(['post_id', 'tag_id']);

            $table->foreign('post_id')->references('id')->on('blog_posts')->cascadeOnDelete();
            $table->foreign('tag_id')->references('id')->on('blog_tags')->cascadeOnDelete();
        });
    }

    public function down()
    {
        Schema::dropIfExists('blog_post_tag');
    }
}
