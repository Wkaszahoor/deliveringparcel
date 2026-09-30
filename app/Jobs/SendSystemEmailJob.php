<?php

namespace App\Jobs;

use App\Models\EmailLog;
use App\Services\EmailService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

/**
 * Sends one templated email identified by its email_logs row. Dispatched on
 * the 'emails' queue when the admin enables queued sending; drained on
 * shared hosting by the cPanel cron (php artisan dp:queue-drain).
 */
class SendSystemEmailJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public int $logId;

    public function __construct(int $logId)
    {
        $this->logId = $logId;
    }

    public function handle(EmailService $emails)
    {
        $log = EmailLog::find($this->logId);

        if (!$log || $log->status === EmailLog::STATUS_SENT) {
            return; // already delivered (idempotent)
        }

        $emails->deliverNow($log);
    }

    public function failed(\Throwable $e)
    {
        $log = EmailLog::find($this->logId);
        if ($log && $log->status !== EmailLog::STATUS_SENT) {
            $log->update([
                'status' => EmailLog::STATUS_FAILED,
                'error'  => 'queue job failed: ' . mb_substr($e->getMessage(), 0, 900),
            ]);
        }
    }
}
