<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCountriesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('countries', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->char('iso2', 2);
            $table->char('iso3', 3);
            $table->string('dial_code', 10)->nullable();
            $table->boolean('is_active')->default(true);
            $table->decimal('shipping_rate', 10, 2)->nullable();
            $table->timestamps();

            $table->unique('iso2');
            $table->unique('iso3');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('countries');
    }
}
