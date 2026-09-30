<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Agent H - Shipping rate engine: zones.
 * New table only (live DB policy: never alter existing tables).
 */
class CreateRateZonesTable extends Migration
{
    public function up()
    {
        if (Schema::hasTable('rate_zones')) {
            return;
        }

        Schema::create('rate_zones', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('code', 20)->unique();
            $table->string('description')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        // Own table only; never executed on the live DB.
        Schema::dropIfExists('rate_zones');
    }
}
