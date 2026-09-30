<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

if (!class_exists('CreateCmsPostTaxonomyTable')) {
    class CreateCmsPostTaxonomyTable extends Migration
    {
        public function up()
        {
            Schema::create('cms_post_taxonomy', function (Blueprint $table) {
                $table->unsignedBigInteger('post_id');
                $table->unsignedBigInteger('taxonomy_id');
                $table->primary(['post_id', 'taxonomy_id']);
                $table->index('taxonomy_id');
            });
        }

        public function down()
        {
            Schema::dropIfExists('cms_post_taxonomy');
        }
    }
} // end class_exists guard

return new CreateCmsPostTaxonomyTable();
