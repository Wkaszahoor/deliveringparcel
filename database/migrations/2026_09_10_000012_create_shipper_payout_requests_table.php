<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

if (!class_exists('CreateShipperPayoutRequestsTable')) {
class CreateShipperPayoutRequestsTable extends Migration
{
    public function up()
    {
        if (Schema::hasTable('shipper_payout_requests')) {
            return;
        }
        Schema::create('shipper_payout_requests', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('shipper_profile_id');
            $table->decimal('amount', 10, 2);
            $table->string('method', 50); // bank/paypal/wise
            $table->text('payment_details')->nullable(); // encrypted details
            $table->enum('status', ['pending', 'processing', 'paid', 'rejected', 'on_hold'])->default('pending');
            $table->text('admin_notes')->nullable();
            $table->unsignedInteger('processed_by')->nullable();
            $table->timestamp('processed_at')->nullable();
            $table->timestamps();
            $table->index(['shipper_profile_id', 'status']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('shipper_payout_requests');
    }
}
} // end class_exists guard
