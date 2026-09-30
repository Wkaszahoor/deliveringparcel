<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

if (!class_exists('CreateShippingRequestsTable')) {
class CreateShippingRequestsTable extends Migration
{
    public function up()
    {
        if (Schema::hasTable('shipping_requests')) {
            return;
        }
        Schema::create('shipping_requests', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('order_id');
            $table->string('reference', 20)->unique(); // DP-SR-0089 auto-generated
            $table->string('customer_username', 20)->nullable(); // CUS-A7K2P1 masked id
            $table->enum('service_type', ['buy_for_me', 'ship_for_me'])->default('buy_for_me');
            $table->string('country_required', 10); // ISO: UK, US, PK, AE
            $table->longText('brief_text');         // auto-generated + admin-editable masked text
            $table->json('product_details');        // full for admin, masked for shippers
            $table->string('contact_email', 200)->nullable();
            $table->string('contact_phone', 50)->nullable();
            $table->string('address_snippet', 300)->nullable(); // city + country only
            $table->decimal('value_range_min', 10, 2)->nullable();
            $table->decimal('value_range_max', 10, 2)->nullable();
            $table->enum('status', [
                'draft', 'open', 'frozen', 'assigned', 'active',
                'proof_pending', 'proof_approved', 'address_pending',
                'dispatched', 'completed', 'cancelled',
            ])->default('draft');
            $table->boolean('is_frozen')->default(false);
            $table->timestamp('frozen_at')->nullable();
            $table->unsignedInteger('frozen_by')->nullable();
            $table->unsignedBigInteger('assigned_shipper_profile_id')->nullable();
            $table->timestamp('assigned_at')->nullable();
            $table->tinyInteger('required_level')->default(1);
            $table->timestamp('expires_at')->nullable();
            $table->unsignedInteger('created_by');
            $table->text('admin_internal_notes')->nullable(); // never shown to shipper/customer
            $table->timestamps();
            $table->softDeletes();
            $table->index(['country_required', 'status', 'is_frozen']);
            $table->index(['order_id']);
            $table->index(['status', 'required_level']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('shipping_requests');
    }
}
} // end class_exists guard
