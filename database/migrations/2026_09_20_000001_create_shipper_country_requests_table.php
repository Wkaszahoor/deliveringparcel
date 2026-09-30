<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

if (!class_exists('CreateShipperCountryRequestsTable')) {
    class CreateShipperCountryRequestsTable extends Migration
    {
        public function up()
        {
            if (Schema::hasTable('shipper_country_requests')) {
                return;
            }
            Schema::create('shipper_country_requests', function (Blueprint $table) {
                $table->id();
                $table->unsignedInteger('shipper_profile_id')->index();
                $table->json('countries');                      // requested full ISO-2 list
                $table->string('note', 500)->nullable();        // shipper's message
                $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending')->index();
                $table->string('admin_note', 500)->nullable();  // admin's reply on reject
                $table->unsignedInteger('reviewed_by')->nullable();
                $table->timestamp('reviewed_at')->nullable();
                $table->timestamps();
            });
        }

        public function down()
        {
            Schema::dropIfExists('shipper_country_requests');
        }
    }
}
