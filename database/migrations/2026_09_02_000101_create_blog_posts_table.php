<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Blog Import System (2026-09-02) — posts imported from WordPress
 * (WXR / atom / RSS / sitemap) or written in the new admin CRUD.
 *
 * NOTE: this is a SEPARATE system from the legacy blog (table `blogs`,
 * model App\Models\Blog). Posts land here and are served publicly
 * under /new/blog (route names newblog.*).
 */
class CreateBlogPostsTable extends Migration
{
    public function up()
    {
        Schema::create('blog_posts', function (Blueprint $table) {
            $table->id();
            $table->string('title', 500);
            $table->string('slug', 500)->unique();
            $table->text('excerpt')->nullable();
            $table->longText('content')->nullable();       // full HTML content
            $table->string('featured_image', 1000)->nullable();     // local path after download
            $table->string('featured_image_url', 1000)->nullable(); // original remote URL
            $table->string('author_name', 255)->nullable();
            $table->string('author_email', 255)->nullable();
            $table->enum('status', ['draft', 'published', 'archived'])->default('draft');
            $table->string('source_url', 1000)->nullable();  // original WordPress URL
            $table->string('source_guid', 1000)->nullable(); // WordPress post GUID
            $table->timestamp('published_at')->nullable();
            $table->timestamp('imported_at')->nullable();
            $table->string('seo_title', 500)->nullable();
            $table->string('seo_description', 500)->nullable();
            $table->string('seo_keywords', 500)->nullable();
            $table->unsignedInteger('view_count')->default(0);
            $table->string('import_batch_id', 36)->nullable(); // UUID of import run
            // unsignedInteger (NOT unsignedBigInteger): the legacy users
            // table uses $table->increments('id') → INT UNSIGNED, and MySQL
            // rejects FK columns whose type/sign doesn't match exactly.
            $table->unsignedInteger('created_by')->nullable();
            $table->unsignedInteger('updated_by')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('created_by')->references('id')->on('users')->nullOnDelete();
            $table->foreign('updated_by')->references('id')->on('users')->nullOnDelete();
            $table->index(['status', 'published_at']);
            $table->index('import_batch_id');
        });
    }

    public function down()
    {
        Schema::dropIfExists('blog_posts');
    }
}
