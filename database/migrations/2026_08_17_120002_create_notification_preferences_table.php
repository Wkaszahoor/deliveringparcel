<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateNotificationPreferencesTable extends Migration
{
    public function up()
    {
        Schema::create('notification_preferences', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->index();
            $table->string('channel', 20)->index();   // in_app | email
            $table->string('type', 60)->index();      // config('admin_notifications.types') keys
            $table->boolean('is_enabled')->default(true);
            $table->timestamps();

            $table->unique(['user_id', 'channel', 'type']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('notification_preferences');
    }
}
