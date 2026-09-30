<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

if (!class_exists('CreateCmsMediaTable')) {
    class CreateCmsMediaTable extends Migration
    {
        public function up()
        {
            Schema::create('cms_media', function (Blueprint $table) {
                $table->id();
                $table->string('filename');                 // original filename
                $table->string('stored_name');              // UUID-based stored filename
                $table->string('path');                     // uploads/cms/2026/09/uuid.jpg
                $table->string('url');                      // full asset() URL cached
                $table->string('mime_type', 100);
                $table->string('extension', 20);
                $table->unsignedInteger('file_size')->default(0);   // bytes
                $table->unsignedInteger('width')->nullable();
                $table->unsignedInteger('height')->nullable();
                $table->string('alt_text')->nullable();
                $table->string('title')->nullable();
                $table->text('caption')->nullable();
                $table->string('folder', 100)->default('uploads/cms');
                $table->unsignedInteger('uploaded_by')->nullable();
                $table->timestamps();
                $table->index(['mime_type', 'created_at']);
            });
        }

        public function down()
        {
            Schema::dropIfExists('cms_media');
        }
    }
} // end class_exists guard

return new CreateCmsMediaTable();
