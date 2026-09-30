<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMobileUiScreensTable extends Migration
{
    public function up()
    {
        Schema::create('mobile_ui_screens', function (Blueprint $table) {
            $table->id();
            $table->string('screen_key', 50)->unique();
            $table->string('screen_name', 100);
            $table->string('parent_key', 50)->nullable(); // sub-screen parent
            $table->integer('screen_order')->default(0);
            $table->enum('global_status', ['active', 'disabled', 'maintenance'])->default('active');
            $table->boolean('admin_visible')->default(true);
            $table->boolean('admin_enabled')->default(true);
            $table->boolean('client_visible')->default(true);
            $table->boolean('client_enabled')->default(true);
            $table->string('icon', 50)->nullable();
            $table->text('description')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('mobile_ui_screens');
    }
}
