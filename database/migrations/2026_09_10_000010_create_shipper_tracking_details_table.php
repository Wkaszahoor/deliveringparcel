<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

if (!class_exists('CreateShipperTrackingDetailsTable')) {
class CreateShipperTrackingDetailsTable extends Migration
{
    public function up()
    {
        if (Schema::hasTable('shipper_tracking_details')) {
            return;
        }
        Schema::create('shipper_tracking_details', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('assignment_id');
            $table->unsignedInteger('order_id');
            $table->string('carrier', 100);
            $table->string('tracking_number', 200);
            $table->string('tracking_url', 500)->nullable();
            $table->date('ship_date');
            $table->date('estimated_delivery')->nullable();
            $table->boolean('admin_reviewed')->default(false);
            $table->boolean('shared_with_customer')->default(false);
            $table->timestamp('shared_at')->nullable();
            $table->unsignedInteger('shared_by')->nullable();
            $table->text('shipper_notes')->nullable();
            $table->text('admin_notes')->nullable();
            $table->timestamps();
            $table->foreign('assignment_id')
                ->references('id')->on('shipper_order_assignments')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('shipper_tracking_details');
    }
}
} // end class_exists guard
