<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMobileUiElementsTable extends Migration
{
    public function up()
    {
        Schema::create('mobile_ui_elements', function (Blueprint $table) {
            $table->id();
            $table->string('section_key', 100)->index();
            $table->string('element_key', 150)->unique();
            $table->string('element_name', 100);
            $table->string('element_type', 50)->default('text'); // text|image|link|badge|pill
            $table->integer('element_order')->default(0);
            $table->enum('global_status', ['active', 'disabled', 'maintenance'])->default('active');
            $table->boolean('admin_visible')->default(true);
            $table->boolean('admin_enabled')->default(true);
            $table->boolean('client_visible')->default(true);
            $table->boolean('client_enabled')->default(true);
            $table->text('description')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('mobile_ui_elements');
    }
}
