<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Blog Import System (2026-09-02) — one row per upload/import run,
 * with counts and a JSON array of {url, error} failure records.
 */
class CreateBlogImportLogsTable extends Migration
{
    public function up()
    {
        Schema::create('blog_import_logs', function (Blueprint $table) {
            $table->id();
            $table->string('batch_id', 36); // UUID
            $table->string('filename', 500);
            $table->enum('file_type', ['atom', 'rss', 'wxr', 'sitemap_xml']);
            $table->enum('status', ['pending', 'processing', 'completed', 'failed', 'partial']);
            $table->unsignedInteger('total_found')->default(0);
            $table->unsignedInteger('total_imported')->default(0);
            $table->unsignedInteger('total_skipped')->default(0);
            $table->unsignedInteger('total_failed')->default(0);
            $table->json('error_log')->nullable(); // array of {url, error} objects
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            // unsignedInteger: matches legacy users.id (INT UNSIGNED) for the FK below.
            $table->unsignedInteger('imported_by')->nullable();
            $table->timestamps();

            $table->foreign('imported_by')->references('id')->on('users')->nullOnDelete();
            $table->index('batch_id');
        });
    }

    public function down()
    {
        Schema::dropIfExists('blog_import_logs');
    }
}
