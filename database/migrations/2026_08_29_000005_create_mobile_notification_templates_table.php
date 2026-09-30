<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMobileNotificationTemplatesTable extends Migration
{
    public function up()
    {
        Schema::create('mobile_notification_templates', function (Blueprint $table) {
            $table->id();
            $table->string('template_name', 100);
            $table->string('notification_type', 50)->default('announcement');
            $table->string('title', 200)->nullable();
            $table->text('body')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('mobile_notification_templates');
    }
}
