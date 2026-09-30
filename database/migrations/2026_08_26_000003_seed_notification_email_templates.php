<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Seed two generic notification-email templates used by TaskNotification
 * and Chatnotification. These replace the stock Laravel MailMessage body
 * with the admin-editable HTML template, while keeping the same placeholder
 * contract ({{title}} {{greeting}} {{description}} {{order_ref}} {{link}}).
 */
class SeedNotificationEmailTemplates extends Migration
{
    public function up()
    {
        $now = now();
        $rows = [
            [
                'key'             => 'notification_task',
                'name'            => 'Order notification email (TaskNotification)',
                'category'        => 'notification',
                'subject'         => '{{title}}',
                'body'            => $this->taskBody(),
                'placeholder_help' => 'Available: {{title}} {{greeting}} {{description}} {{order_ref}} {{order_id}} {{link}} + any field the sending flow passes',
                'is_enabled'      => true,
                'created_at'      => $now,
                'updated_at'      => $now,
            ],
            [
                'key'             => 'notification_chat',
                'name'            => 'Chat notification email (Chatnotification)',
                'category'        => 'notification',
                'subject'         => '{{title}}',
                'body'            => $this->chatBody(),
                'placeholder_help' => 'Available: {{title}} {{greeting}} {{body}} {{description}} {{order_ref}} {{order_id}} {{link}}',
                'is_enabled'      => true,
                'created_at'      => $now,
                'updated_at'      => $now,
            ],
        ];

        foreach ($rows as $row) {
            DB::table('email_templates')->insert($row);
        }
    }

    public function down()
    {
        DB::table('email_templates')->whereIn('key', ['notification_task', 'notification_chat'])->delete();
    }

    private function taskBody(): string
    {
        return <<<'HTML'
<div style="font-family:Arial,sans-serif;max-width:600px;margin:0 auto;">
  <div style="background:#1a1a2e;color:#fff;padding:20px 24px;border-radius:6px 6px 0 0;">
    <h2 style="margin:0;font-size:18px;">{{greeting}}</h2>
  </div>
  <div style="background:#fff;padding:24px;border:1px solid #e2e8f0;border-top:none;border-radius:0 0 6px 6px;">
    <p style="font-size:15px;color:#333;margin-top:0;">{{description}}</p>
    <p style="font-size:15px;color:#333;">Order: <strong>{{order_ref}}</strong></p>
    <a href="{{link}}" style="display:inline-block;background:#1a1a2e;color:#fff;padding:10px 24px;border-radius:4px;text-decoration:none;font-size:14px;margin-top:12px;">View Order</a>
    <hr style="border:none;border-top:1px solid #e2e8f0;margin:24px 0 16px;">
    <p style="font-size:12px;color:#999;">DeliveringParcel — Delivering Worldwide</p>
  </div>
</div>
HTML;
    }

    private function chatBody(): string
    {
        return <<<'HTML'
<div style="font-family:Arial,sans-serif;max-width:600px;margin:0 auto;">
  <div style="background:#1a1a2e;color:#fff;padding:20px 24px;border-radius:6px 6px 0 0;">
    <h2 style="margin:0;font-size:18px;">{{greeting}}</h2>
  </div>
  <div style="background:#fff;padding:24px;border:1px solid #e2e8f0;border-top:none;border-radius:0 0 6px 6px;">
    <p style="font-size:15px;color:#333;margin-top:0;">Order: <strong>{{order_ref}}</strong></p>
    <div style="background:#f7f8fa;padding:16px;border-radius:6px;border-left:3px solid #1a1a2e;margin:16px 0;">
      <p style="font-size:14px;color:#444;margin:0;">{{body}}</p>
    </div>
    <a href="{{link}}" style="display:inline-block;background:#1a1a2e;color:#fff;padding:10px 24px;border-radius:4px;text-decoration:none;font-size:14px;margin-top:12px;">View Conversation</a>
    <hr style="border:none;border-top:1px solid #e2e8f0;margin:24px 0 16px;">
    <p style="font-size:12px;color:#999;">DeliveringParcel — Delivering Worldwide</p>
  </div>
</div>
HTML;
    }
}
