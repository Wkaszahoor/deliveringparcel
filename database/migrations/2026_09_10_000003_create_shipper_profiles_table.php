<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

if (!class_exists('CreateShipperProfilesTable')) {
class CreateShipperProfilesTable extends Migration
{
    public function up()
    {
        if (Schema::hasTable('shipper_profiles')) {
            return;
        }
        Schema::create('shipper_profiles', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('user_id')->unique();
            $table->tinyInteger('level')->default(1); // 1=Buy for Me, 2=Ship for Me (max 10), 3=Unlimited
            $table->enum('status', ['pending', 'active', 'suspended', 'banned'])->default('pending');
            $table->json('service_countries')->nullable();   // ["UK","US","PK","AE"]
            $table->json('services_offered')->nullable();    // {"buy_for_me":true,...}
            $table->string('residence_type', 50)->nullable(); // apartment/house/villa/office
            $table->boolean('has_storage')->default(false);
            $table->integer('max_concurrent_orders')->default(3);
            $table->integer('current_active_orders')->default(0);
            $table->decimal('wallet_balance', 12, 2)->default(0);
            $table->decimal('wallet_pending', 12, 2)->default(0);
            $table->decimal('total_earned', 12, 2)->default(0);
            $table->decimal('rating', 3, 2)->default(0);
            $table->unsignedInteger('total_ratings')->default(0);
            $table->unsignedInteger('total_completed')->default(0);
            $table->enum('kyc_status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->string('payout_method', 50)->nullable(); // bank/paypal/wise
            $table->text('payout_details_encrypted')->nullable();
            $table->json('social_links')->nullable();
            $table->json('reference_1')->nullable(); // {name, phone, facebook_id}
            $table->json('reference_2')->nullable();
            $table->timestamp('verified_at')->nullable();
            $table->timestamp('suspended_at')->nullable();
            $table->text('suspension_reason')->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->index(['status', 'level']);
            $table->index('kyc_status');
        });
    }

    public function down()
    {
        Schema::dropIfExists('shipper_profiles');
    }
}
} // end class_exists guard
