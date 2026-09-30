<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateRequestQuotesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('request_quotes', function (Blueprint $table) {
            $table->id();
            $table->text('name');
            $table->text('number');
            $table->text('email');
            $table->text('cargotype');
            $table->text('country');
            $table->text('destination');
            $table->text('weight');
            $table->text('width');
            $table->text('height');
            $table->text('detail');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('request_quotes');
    }
}
