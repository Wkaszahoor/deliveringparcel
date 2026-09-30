<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

if (!class_exists('CreateSeoAnalysesTable')) {
    class CreateSeoAnalysesTable extends Migration
    {
        public function up()
        {
            if (Schema::hasTable('seo_analyses')) {
                return;
            }
            Schema::create('seo_analyses', function (Blueprint $table) {
                $table->id();
                $table->string('uuid')->unique()->nullable();
                $table->unsignedInteger('user_id')->nullable()->index();    // admin who ran it
                $table->string('source_type', 20);                          // post|url|content
                $table->text('source_url')->nullable();                     // when source_type = url
                $table->string('content_type', 30)->nullable();             // report['post']['type']
                $table->unsignedInteger('subject_id')->nullable()->index(); // cms_posts.id
                $table->string('subject_title')->nullable();
                $table->string('target_keyword')->nullable();               // focus topic
                $table->string('status', 20)->default('completed');
                $table->smallInteger('overall_score')->nullable();
                $table->string('grade', 40)->nullable();
                $table->json('categories')->nullable();                     // category => score
                $table->json('report')->nullable();                         // full analyzer report
                $table->integer('word_count')->default(0);
                $table->string('reading_time', 20)->nullable();
                $table->string('analyzer_version', 10)->default('2.0.0');
                $table->string('fetch_status', 20)->nullable();             // ok|failed (url mode)
                $table->smallInteger('fetch_http_status')->nullable();
                $table->timestamps();
                $table->index('created_at');
            });
        }

        public function down()
        {
            Schema::dropIfExists('seo_analyses');
        }
    }
}
