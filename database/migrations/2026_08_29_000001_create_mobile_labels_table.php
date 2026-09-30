<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMobileLabelsTable extends Migration
{
    public function up()
    {
        Schema::create('mobile_labels', function (Blueprint $table) {
            $table->id();
            $table->string('label_key', 100)->unique();
            $table->string('label_value', 255)->nullable();
            $table->string('description', 500)->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('mobile_labels');
    }
}
