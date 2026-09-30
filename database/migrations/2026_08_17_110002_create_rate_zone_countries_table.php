<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Agent H - zone -> country mappings.
 * country_id references countries.id (Agent C table) as a plain
 * unsignedBigInteger (no hard FK, table may land later).
 * country_code is the free-text ISO2 fallback when countries is absent.
 */
class CreateRateZoneCountriesTable extends Migration
{
    public function up()
    {
        if (Schema::hasTable('rate_zone_countries')) {
            return;
        }

        Schema::create('rate_zone_countries', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('rate_zone_id')->index();
            $table->unsignedBigInteger('country_id')->nullable()->index();
            $table->string('country_code', 8)->nullable()->index();
            $table->timestamps();

            $table->foreign('rate_zone_id')
                  ->references('id')->on('rate_zones')
                  ->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('rate_zone_countries');
    }
}
