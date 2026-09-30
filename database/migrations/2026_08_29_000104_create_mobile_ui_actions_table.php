<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMobileUiActionsTable extends Migration
{
    public function up()
    {
        Schema::create('mobile_ui_actions', function (Blueprint $table) {
            $table->id();
            $table->string('section_key', 100)->index();
            $table->string('action_key', 150)->unique();
            $table->string('action_name', 100);
            $table->string('action_label', 100);
            $table->string('action_type', 50)->default('api_call'); // navigate|api_call|modal|link
            $table->integer('action_order')->default(0);
            $table->enum('global_status', ['active', 'disabled', 'maintenance'])->default('active');
            $table->boolean('admin_visible')->default(true);
            $table->boolean('admin_enabled')->default(true);
            $table->boolean('client_visible')->default(true);
            $table->boolean('client_enabled')->default(true);
            $table->json('allowed_order_statuses')->nullable(); // []/null = always show
            $table->boolean('confirmation_required')->default(false);
            $table->string('confirmation_message', 500)->nullable();
            $table->string('icon', 50)->nullable();
            $table->string('color', 7)->nullable();
            $table->text('description')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('mobile_ui_actions');
    }
}
