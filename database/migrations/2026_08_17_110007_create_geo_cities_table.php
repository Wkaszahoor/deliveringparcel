<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Agent H - geography: cities per state.
 */
class CreateGeoCitiesTable extends Migration
{
    public function up()
    {
        if (Schema::hasTable('geo_cities')) {
            return;
        }

        Schema::create('geo_cities', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('geo_state_id')->index();
            $table->string('name');
            $table->boolean('is_active')->default(true)->index();
            $table->timestamps();

            $table->foreign('geo_state_id')
                  ->references('id')->on('geo_states')
                  ->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('geo_cities');
    }
}
