<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMobileNotificationLogsTable extends Migration
{
    public function up()
    {
        Schema::create('mobile_notification_logs', function (Blueprint $table) {
            $table->id();
            // users.id is int unsigned (legacy schema) — match it for the FK.
            $table->unsignedInteger('sent_by')->nullable();
            $table->foreign('sent_by')->references('id')->on('users')->nullOnDelete();
            $table->string('target_type', 20)->default('all'); // all|role|user|order
            $table->string('target_value', 100)->nullable();
            $table->string('notification_type', 50)->default('announcement');
            $table->string('title', 200);
            $table->text('body')->nullable();
            $table->timestamp('scheduled_at')->nullable();
            $table->timestamp('sent_at')->nullable();
            $table->string('status', 20)->default('pending'); // pending|sent|failed
            $table->integer('recipient_count')->default(0);
            $table->timestamps();

            $table->index(['status', 'scheduled_at']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('mobile_notification_logs');
    }
}
