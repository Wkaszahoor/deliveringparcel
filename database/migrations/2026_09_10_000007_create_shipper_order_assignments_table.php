<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

if (!class_exists('CreateShipperOrderAssignmentsTable')) {
class CreateShipperOrderAssignmentsTable extends Migration
{
    public function up()
    {
        if (Schema::hasTable('shipper_order_assignments')) {
            return;
        }
        Schema::create('shipper_order_assignments', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('order_id');
            $table->unsignedBigInteger('request_id')->nullable();
            $table->unsignedBigInteger('shipper_profile_id');
            $table->decimal('shipper_fee', 10, 2)->default(0);   // shipper earns — never shown to customer
            $table->decimal('platform_fee', 10, 2)->default(0);  // margin — never shown to shipper
            $table->decimal('total_charged', 10, 2)->default(0); // customer paid — never shown to shipper
            $table->enum('status', [
                'assigned', 'accepted', 'purchasing', 'purchased',
                'awaiting_package', 'package_received', 'proof_uploaded',
                'proof_approved', 'address_received', 'address_forwarded',
                'dispatched', 'tracking_added', 'tracking_shared',
                'delivered', 'completed', 'disputed', 'cancelled',
            ])->default('assigned');
            $table->timestamp('purchase_deadline')->nullable(); // Buy for Me: 2 working days
            $table->timestamp('purchased_at')->nullable();
            $table->timestamp('package_received_at')->nullable();
            $table->timestamp('dispatched_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->decimal('wallet_credit_amount', 10, 2)->default(0);
            $table->decimal('wallet_hold_amount', 10, 2)->default(0); // 20% dispute buffer
            $table->timestamp('hold_release_at')->nullable(); // release 7 days post-delivery
            $table->text('admin_notes')->nullable();
            $table->timestamps();
            $table->index(['order_id']);
            $table->index(['shipper_profile_id', 'status']);
            $table->index(['status']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('shipper_order_assignments');
    }
}
} // end class_exists guard
