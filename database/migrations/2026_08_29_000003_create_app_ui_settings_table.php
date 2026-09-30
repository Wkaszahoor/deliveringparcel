<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAppUiSettingsTable extends Migration
{
    public function up()
    {
        Schema::create('app_ui_settings', function (Blueprint $table) {
            $table->id();
            $table->string('setting_key', 50)->unique();
            $table->text('setting_value')->nullable();
            $table->string('setting_group', 50)->default('general');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('app_ui_settings');
    }
}
