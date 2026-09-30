<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

if (!class_exists('CreateShipperQuotesTable')) {
class CreateShipperQuotesTable extends Migration
{
    public function up()
    {
        if (Schema::hasTable('shipper_quotes')) {
            return;
        }
        Schema::create('shipper_quotes', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('request_id');
            $table->unsignedBigInteger('shipper_profile_id');
            $table->decimal('quoted_amount', 10, 2);
            $table->integer('estimated_days');
            $table->text('notes')->nullable(); // shipper → admin only, never raw-forwarded
            $table->enum('status', ['pending', 'accepted', 'rejected', 'withdrawn'])->default('pending');
            $table->text('admin_response')->nullable();
            $table->timestamp('responded_at')->nullable();
            $table->timestamps();
            $table->unique(['request_id', 'shipper_profile_id']); // one quote per shipper per request
            $table->foreign('request_id')
                ->references('id')->on('shipping_requests')->onDelete('cascade');
            $table->index(['request_id', 'status']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('shipper_quotes');
    }
}
} // end class_exists guard
