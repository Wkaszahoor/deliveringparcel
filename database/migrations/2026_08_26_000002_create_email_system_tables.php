<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Unified email system (2026-08-26):
 *  - email_templates: admin-managed subject/body per email key. A row is an
 *    OPTIONAL override — when absent, senders keep their original mailable
 *    (legacy behavior preserved). Placeholders: {{name}} {{email}} {{order_ref}}
 *    {{order_id}} {{amount}} {{link}} + any extra data keys passed by callers.
 *  - email_logs: business-level delivery history (queued/sent/failed,
 *    attempts, error) — visible in Admin → Communications → Email Logs.
 * Sending is also gated by the email_master_enabled setting and the
 * per-template is_enabled flag; a mail failure NEVER fails the business flow.
 */
class CreateEmailSystemTables extends Migration
{
    public function up()
    {
        Schema::create('email_templates', function (Blueprint $table) {
            $table->id();
            $table->string('key', 100)->unique();      // e.g. welcome_email
            $table->string('name', 191);               // admin label
            $table->string('category', 50)->default('system'); // registration|order|offer|payment|notification|message|blog|system
            $table->string('subject', 255);
            $table->longText('body');
            $table->text('placeholder_help')->nullable();
            $table->boolean('is_enabled')->default(true);
            $table->timestamps();
        });

        Schema::create('email_logs', function (Blueprint $table) {
            $table->id();
            $table->string('template_key', 100)->index();
            $table->string('to_email', 255)->index();
            $table->string('to_name', 255)->nullable();
            $table->unsignedBigInteger('order_id')->nullable()->index();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('subject', 255);
            $table->string('status', 20)->default('queued')->index(); // queued|sent|failed|skipped
            $table->unsignedTinyInteger('attempts')->default(0);
            $table->text('error')->nullable();
            $table->timestamp('queued_at')->nullable();
            $table->timestamp('sent_at')->nullable()->index();
            $table->timestamps();
        });

        // Starter templates for the flows wired through EmailService.
        // Deleting a row makes its sender fall back to the ORIGINAL legacy
        // mailable — nothing is ever lost.
        $now = now();
        $templates = [
            ['welcome_email', 'Welcome email', 'registration', 'Welcome to DeliveringParcel, {{name}}!',
             "Hi {{name}},\n\nYour DeliveringParcel account is ready. Sign in to get your forwarding address, request quotes and manage shipments.\n\n<a href=\"{{link}}\">Sign in</a>\n\nIf the button does not work, open: {{link}}"],
            ['contact_submitted_admin', 'Contact form submitted (admin alert)', 'message', 'New contact message from {{name}}',
             "New contact form submission:\n\nName: {{name}}\nEmail: {{email}}\nNumber: {{number}}\nAddress: {{address}}\nMessage: {{message}}"],
            ['quote_submitted_admin', 'Free quote requested (admin alert)', 'message', 'New quote request from {{name}}',
             "New free-quote request:\n\nName: {{name}}\nEmail: {{email}}\nNumber: {{number}}\nCargo type: {{cargotype}}\nFrom: {{country}}\nTo: {{destination}}\nWeight: {{weight}}\nDimensions: {{width}} x {{height}}\nDetails: {{detail}}"],
            ['guest_account_credentials', 'Guest auto-account credentials', 'registration', 'Your DeliveringParcel account',
             "Hi {{title}},\n\nWe created a DeliveringParcel account for you so you can track your request.\n\nEmail: {{email}}\nPassword: {{password}}\n\n<a href=\"{{url}}\">Sign in here</a>\n\nPlease change your password after your first login."],
        ];
        foreach ($templates as [$key, $name, $category, $subject, $body]) {
            DB::table('email_templates')->insert([
                'key' => $key, 'name' => $name, 'category' => $category,
                'subject' => $subject, 'body' => $body,
                'placeholder_help' => 'Available: {{name}} {{email}} {{title}} {{order_id}} {{order_ref}} {{link}} {{url}} + any field the sending flow passes',
                'is_enabled' => true, 'created_at' => $now, 'updated_at' => $now,
            ]);
        }
    }

    public function down()
    {
        Schema::dropIfExists('email_logs');
        Schema::dropIfExists('email_templates');
    }
}
