<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Agent H - geography: states per country.
 * country_id references countries.id (Agent C) as plain column, no hard FK.
 */
class CreateGeoStatesTable extends Migration
{
    public function up()
    {
        if (Schema::hasTable('geo_states')) {
            return;
        }

        Schema::create('geo_states', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('country_id')->nullable()->index();
            $table->string('country_code', 8)->nullable()->index(); // fallback when countries absent
            $table->string('name');
            $table->string('code', 20)->nullable();
            $table->boolean('is_active')->default(true)->index();
            $table->timestamps();

            $table->index(['country_id', 'is_active']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('geo_states');
    }
}
