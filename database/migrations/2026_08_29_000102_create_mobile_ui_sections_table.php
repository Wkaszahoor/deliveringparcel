<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMobileUiSectionsTable extends Migration
{
    public function up()
    {
        Schema::create('mobile_ui_sections', function (Blueprint $table) {
            $table->id();
            $table->string('screen_key', 50)->index();
            $table->string('section_key', 100)->unique();
            $table->string('section_name', 100);
            $table->integer('section_order')->default(0);
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
        Schema::dropIfExists('mobile_ui_sections');
    }
}
