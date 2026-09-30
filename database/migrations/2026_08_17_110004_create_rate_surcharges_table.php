<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Agent H - global surcharges (fuel %, flat fees...).
 */
class CreateRateSurchargesTable extends Migration
{
    public function up()
    {
        if (Schema::hasTable('rate_surcharges')) {
            return;
        }

        Schema::create('rate_surcharges', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->enum('type', ['percentage', 'fixed'])->default('percentage');
            $table->decimal('value', 10, 2)->default(0);
            $table->enum('applies_to', ['all', 'fuel', 'insurance', 'remote'])->default('all');
            $table->boolean('is_active')->default(true)->index();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('rate_surcharges');
    }
}
