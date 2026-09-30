<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Creates the email_logs ledger used by EmailService (every outgoing
 * templated email is recorded here). Production was missing this table —
 * EmailService::send() 500'd order creation until this migration runs
 * (the service is now also fail-safe without it).
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('email_logs')) {
            return;
        }

        Schema::create('email_logs', function (Blueprint $table) {
            $table->id();
            $table->string('template_key', 100);
            $table->string('to_email', 255);
            $table->string('to_name')->nullable();
            $table->unsignedBigInteger('order_id')->nullable();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('subject')->nullable();
            $table->string('status', 20)->default('queued'); // queued|sent|failed|skipped
            $table->unsignedTinyInteger('attempts')->default(0);
            $table->text('error')->nullable();
            $table->timestamp('queued_at')->nullable();
            $table->timestamp('sent_at')->nullable();
            $table->timestamps();

            $table->index('status');
            $table->index('to_email');
            $table->index('order_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('email_logs');
    }
};
