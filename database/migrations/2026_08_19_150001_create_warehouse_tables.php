<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateWarehouseTables extends Migration
{
    public function up()
    {
        Schema::create('warehouse_bins', function (Blueprint $t) {
            $t->id();
            $t->string('code', 30)->unique();          // e.g. A-01-1
            $t->string('zone', 50)->nullable()->index();
            $t->unsignedInteger('capacity')->default(100);
            $t->text('notes')->nullable();
            $t->boolean('is_active')->default(true)->index();
            $t->timestamps();
        });

        Schema::create('warehouse_packages', function (Blueprint $t) {
            $t->id();
            $t->unsignedBigInteger('user_id')->nullable()->index();
            $t->string('expected_tracking', 100)->nullable()->index();
            $t->timestamp('received_at')->nullable();
            $t->string('status', 20)->default('pending')->index(); // pending|received|damaged|returned
            $t->unsignedBigInteger('storage_bin_id')->nullable()->index();
            $t->text('notes')->nullable();
            $t->json('photos')->nullable();              // uploads/warehouse/
            $t->unsignedBigInteger('created_by')->nullable();
            $t->timestamps();
        });

        Schema::create('warehouse_shipments', function (Blueprint $t) {
            $t->id();
            $t->string('code', 30)->unique();
            $t->unsignedBigInteger('user_id')->nullable()->index();
            $t->string('carrier_name', 100)->nullable();
            $t->string('tracking_number', 100)->nullable()->index();
            $t->string('status', 20)->default('preparing')->index(); // preparing|dispatched|in_transit|delivered|exception
            $t->timestamp('dispatched_at')->nullable();
            $t->timestamp('delivered_at')->nullable();
            $t->json('address_snapshot')->nullable();
            $t->text('notes')->nullable();
            $t->timestamps();
        });

        Schema::create('warehouse_shipment_events', function (Blueprint $t) {
            $t->id();
            $t->unsignedBigInteger('shipment_id')->index();
            $t->string('status', 20);
            $t->string('note', 500)->nullable();
            $t->timestamps();
        });

        Schema::create('package_shipment', function (Blueprint $t) {
            $t->id();
            $t->unsignedBigInteger('package_id')->index();
            $t->unsignedBigInteger('shipment_id')->index();
            $t->timestamps();
            $t->unique(['package_id', 'shipment_id']);
        });
    }

    public function down()
    {
        foreach (['package_shipment', 'warehouse_shipment_events', 'warehouse_shipments', 'warehouse_packages', 'warehouse_bins'] as $t) {
            Schema::dropIfExists($t);
        }
    }
}
