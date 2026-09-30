<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddCountryToTestimonialsTable extends Migration
{
    public function up()
    {
        if (Schema::hasTable('testimonials') && !Schema::hasColumn('testimonials', 'country')) {
            Schema::table('testimonials', function (Blueprint $table) {
                $table->string('country', 100)->nullable()->index()->after('role_or_company');
            });
        }
    }

    public function down()
    {
        if (Schema::hasTable('testimonials') && Schema::hasColumn('testimonials', 'country')) {
            Schema::table('testimonials', function (Blueprint $table) {
                $table->dropColumn('country');
            });
        }
    }
}
