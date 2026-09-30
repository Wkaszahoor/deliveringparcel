<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Agent H - rate matrix rules.
 * Higher priority wins when several rules match a shipment.
 */
class CreateRateRulesTable extends Migration
{
    public function up()
    {
        if (Schema::hasTable('rate_rules')) {
            return;
        }

        Schema::create('rate_rules', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('origin_zone_id')->index();
            $table->unsignedBigInteger('destination_zone_id')->index();
            $table->string('service_type', 50)->index();
            $table->decimal('weight_min', 10, 2)->default(0);
            $table->decimal('weight_max', 10, 2)->default(0);
            $table->decimal('base_price', 10, 2)->default(0);
            $table->decimal('per_kg_price', 10, 2)->default(0);
            $table->unsignedInteger('transit_days_min')->nullable();
            $table->unsignedInteger('transit_days_max')->nullable();
            $table->boolean('is_active')->default(true)->index();
            $table->integer('priority')->default(0)->index();
            $table->date('valid_from')->nullable();
            $table->date('valid_to')->nullable();
            $table->timestamps();

            $table->foreign('origin_zone_id')
                  ->references('id')->on('rate_zones')
                  ->onDelete('cascade');
            $table->foreign('destination_zone_id')
                  ->references('id')->on('rate_zones')
                  ->onDelete('cascade');
            $table->index(['origin_zone_id', 'destination_zone_id', 'service_type', 'is_active'], 'rate_rules_lookup_idx');
        });
    }

    public function down()
    {
        Schema::dropIfExists('rate_rules');
    }
}
