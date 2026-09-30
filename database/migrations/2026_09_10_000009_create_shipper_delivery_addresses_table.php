<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

if (!class_exists('CreateShipperDeliveryAddressesTable')) {
class CreateShipperDeliveryAddressesTable extends Migration
{
    public function up()
    {
        if (Schema::hasTable('shipper_delivery_addresses')) {
            return;
        }
        Schema::create('shipper_delivery_addresses', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('order_id');
            $table->unsignedBigInteger('assignment_id');
            $table->unsignedInteger('submitted_by_user_id');
            $table->string('recipient_name', 200);
            $table->string('address_line_1', 300);
            $table->string('address_line_2', 300)->nullable();
            $table->string('city', 100);
            $table->string('state', 100)->nullable();
            $table->string('postal_code', 20);
            $table->string('country', 100);
            $table->string('phone', 50)->nullable();
            $table->string('email', 200)->nullable();
            $table->text('delivery_instructions')->nullable();
            // Admin review
            $table->boolean('admin_reviewed')->default(false);
            $table->unsignedInteger('admin_reviewed_by')->nullable();
            $table->text('admin_review_notes')->nullable();
            // Forwarding to shipper
            $table->boolean('forwarded_to_shipper')->default(false);
            $table->timestamp('forwarded_at')->nullable();
            $table->unsignedInteger('forwarded_by')->nullable();
            $table->enum('forward_level', [
                'full',          // full address + phone
                'address_only',  // no phone
                'city_country',  // minimal — low-trust shippers
            ])->default('full');
            $table->timestamps();
            $table->index(['order_id']);
            $table->index(['assignment_id']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('shipper_delivery_addresses');
    }
}
} // end class_exists guard
