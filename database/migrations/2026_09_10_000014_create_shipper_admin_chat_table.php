<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

if (!class_exists('CreateShipperAdminChatTable')) {
class CreateShipperAdminChatTable extends Migration
{
    public function up()
    {
        if (Schema::hasTable('shipper_admin_chat')) {
            return;
        }
        Schema::create('shipper_admin_chat', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('assignment_id')->nullable();
            $table->unsignedBigInteger('request_id')->nullable();
            $table->unsignedInteger('order_id')->nullable();
            $table->enum('from_type', ['admin', 'shipper']);
            $table->unsignedInteger('from_id');
            $table->text('message');
            $table->string('attachment_path', 500)->nullable(); // PRIVATE disk
            $table->boolean('is_read')->default(false);
            $table->timestamp('read_at')->nullable();
            $table->timestamps();
            $table->index(['assignment_id', 'is_read']);
            $table->index(['request_id', 'is_read']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('shipper_admin_chat');
    }
}
} // end class_exists guard
