<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAppScreenSettingsTable extends Migration
{
    public function up()
    {
        Schema::create('app_screen_settings', function (Blueprint $table) {
            $table->id();
            $table->string('screen_key', 50)->unique();
            $table->string('screen_name', 100);
            $table->string('parent_screen', 50)->nullable();
            $table->boolean('is_visible')->default(true);
            $table->boolean('is_enabled')->default(true);
            $table->json('visible_to')->nullable(); // MySQL strict: no JSON defaults; seeder always populates
            $table->integer('screen_order')->default(0);
            $table->json('config')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('app_screen_settings');
    }
}
