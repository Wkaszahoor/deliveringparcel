<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Agent H - insurance coverage tiers by declared value.
 * declared_value_max NULL = open-ended top tier.
 */
class CreateRateInsurancesTable extends Migration
{
    public function up()
    {
        if (Schema::hasTable('rate_insurances')) {
            return;
        }

        Schema::create('rate_insurances', function (Blueprint $table) {
            $table->id();
            $table->decimal('declared_value_min', 12, 2)->default(0);
            $table->decimal('declared_value_max', 12, 2)->nullable();
            $table->decimal('cost', 10, 2)->default(0);
            $table->boolean('is_active')->default(true)->index();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('rate_insurances');
    }
}
