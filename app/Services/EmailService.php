<?php

namespace App\Services;

use App\Mail\SystemEmail;
use App\Models\EmailLog;
use App\Models\EmailTemplate;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Throwable;

/**
 * Unified email pipeline (legacy + new design share it):
 *
 *   business flow → EmailService::send(key, recipient, data)
 *        → master switch + per-template switch checked
 *        → admin template rendered ({{placeholders}}) — or the caller's
 *          ORIGINAL mailable when no template row exists (legacy parity)
 *        → sent inline OR queued ('emails' queue → cPanel cron drains it)
 *        → every attempt recorded in email_logs (Admin → Communications)
 *
 * CONTRACT: a mail failure NEVER throws to the caller — the order/offer/
 * payment flow must keep working when SMTP is down. send() returns bool.
 */
class EmailService
{
    public static function masterEnabled(): bool
    {
        try {
            return \App\Models\Setting::getBool('email_master_enabled', true);
        } catch (\Throwable $e) {
            return true;
        }
    }

    /** Queue only when an admin explicitly enabled it (cron worker required). */
    public static function useQueue(): bool
    {
        try {
            return \App\Models\Setting::getBool('email_use_queue', false);
        } catch (\Throwable $e) {
            return false;
        }
    }

    /**
     * @param string          $key      template key (EmailTemplate::KNOWN_KEYS)
     * @param mixed           $recipient User model | email string | ['email'=>..,'name'=>..]
     * @param array           $data     extra placeholder values (stringable)
     * @param int|null        $orderId  links the log to an order
     * @param \Closure|null   $fallback original mailable sender, used when no
     *                                  admin template row exists for $key
     */
    public function send(string $key, $recipient, array $data = [], ?int $orderId = null, ?\Closure $fallback = null): bool
    {
        if (!self::masterEnabled()) {
            return false; // admin killed all outgoing email — by design, no log spam
        }

        [$email, $name, $userId] = $this->resolveRecipient($recipient);
        if ($email === null) {
            return false;
        }

        $template = EmailTemplate::activeFor($key);

        // No admin template → keep the sender's original mailable (legacy
        // behavior), but still record the outcome in the log.
        if ($template === null) {
            if ($fallback === null) {
                return false;
            }

            $log = $this->createLog([
                'template_key' => $key,
                'to_email'     => $email,
                'to_name'      => $name,
                'order_id'     => $orderId,
                'user_id'      => $userId,
                'subject'      => '(legacy mailable: ' . $key . ')',
                'status'       => EmailLog::STATUS_QUEUED,
            ]);

            // Logging unavailable (e.g. missing table) → still send, never
            // let the log break the business flow.
            if ($log === null) {
                try { $fallback(); return true; }
                catch (\Throwable $e) { report($e); return false; }
            }

            return $this->runFallback($log, $fallback);
        }

        $vars = $this->buildVars($email, $name, $orderId, $data);

        $log = $this->createLog([
            'template_key' => $key,
            'to_email'     => $email,
            'to_name'      => $name,
            'order_id'     => $orderId,
            'user_id'      => $userId,
            'subject'      => $this->render($template->subject, $vars),
            'status'       => EmailLog::STATUS_QUEUED,
        ]);

        // Logging unavailable → deliver directly without a ledger row.
        if ($log === null) {
            try {
                $subject = $this->render($template->subject, $vars);
                $body    = $this->render($template->body, $vars);
                if (!Str::contains($body, '<')) {
                    $body = nl2br(e($body));
                }
                Mail::to($email, $name ?: null)->send(new SystemEmail($subject, $body));
                return true;
            } catch (\Throwable $e) {
                report($e);
                return false;
            }
        }

        if (self::useQueue()) {
            \App\Jobs\SendSystemEmailJob::dispatch($log->id)->onQueue('emails');
            return true; // still 'queued' in the log; job finalizes it
        }

        return $this->deliverNow($log);
    }

    /** Fan-out to the (up to 5) admin accounts — mirrors PaymentService. */
    public function sendToAdmins(string $key, array $data = [], ?int $orderId = null, ?\Closure $fallback = null): void
    {
        try {
            $admins = User::where('type', 'admin')->limit(5)->get();
        } catch (\Throwable $e) {
            return;
        }

        foreach ($admins as $admin) {
            $this->send($key, $admin, $data, $orderId, $fallback ? $fallback($admin) : null);
        }
    }

    /** Admin-triggered retry of a failed log row. */
    public function retry(EmailLog $log): bool
    {
        if (!in_array($log->status, [EmailLog::STATUS_FAILED, EmailLog::STATUS_QUEUED], true)) {
            return false;
        }

        return $this->deliverNow($log);
    }

    /**
     * Immediate delivery of a templated log row (also used by the queue job).
     * Safe against a missing/renamed template at retry time.
     */
  /**  public function deliverNow(EmailLog $log): bool
    {
        $template = EmailTemplate::activeFor($log->template_key);
        if ($template === null) {
            $log->update([
                'status' => EmailLog::STATUS_FAILED,
                'attempts' => $log->attempts + 1,
                'error' => 'No active template for key "' . $log->template_key . '" (edited or removed).',
            ]);
            return false;
        }

        $vars = $this->buildVars($log->to_email, $log->to_name, $log->order_id, []);
        $subject = $this->render($template->subject, $vars);
        $body = $this->render($template->body, $vars);
        if (!Str::contains($body, '<')) {
            $body = nl2br(e($body)); // plain-text template → safe paragraphs
        }

        $log->subject = $subject;

        return $this->attempt($log, function () use ($subject, $body) {
            Mail::to($log->to_email, $log->to_name ?: null)->send(new SystemEmail($subject, $body));
        });
    }
*/



public function deliverNow(EmailLog $log): bool
{
    $template = EmailTemplate::activeFor($log->template_key);

    if ($template === null) {
        $log->update([
            'status' => EmailLog::STATUS_FAILED,
            'attempts' => $log->attempts + 1,
            'error' => 'No active template for key "' . $log->template_key . '" (edited or removed).',
        ]);

        return false;
    }

    $vars = $this->buildVars(
        $log->to_email,
        $log->to_name,
        $log->order_id,
        []
    );

    $subject = $this->render($template->subject, $vars);
    $body = $this->render($template->body, $vars);

    if (!Str::contains($body, '<')) {
        $body = nl2br(e($body));
    }

    $log->subject = $subject;

    return $this->attempt($log, function () use ($log, $subject, $body) {
        Mail::to(
            $log->to_email,
            $log->to_name ?: null
        )->send(
            new SystemEmail($subject, $body)
        );
    });
}
    /* ------------------------------------------------------------------ */

    /**
     * Fail-safe log writer — a missing/full/broken email_logs table must
     * NEVER 500 the calling flow (prod crashed on order creation before
     * this table existed). Returns null when logging is unavailable.
     */
    protected function createLog(array $attrs): ?EmailLog
    {
        try {
            return EmailLog::create($attrs);
        } catch (\Throwable $e) {
            report($e);
            return null;
        }
    }

    protected function runFallback(EmailLog $log, \Closure $fallback): bool
    {
        return $this->attempt($log, $fallback);
    }

    protected function attempt(EmailLog $log, \Closure $work): bool
    {
        $log->attempts = $log->attempts + 1;

        try {
            $work();
            $log->status = EmailLog::STATUS_SENT;
            $log->sent_at = now();
            $log->error = null;
            $log->save();
            return true;
        } catch (Throwable $e) {
            $log->status = EmailLog::STATUS_FAILED;
            $log->error = Str::limit($e->getMessage(), 900);
            $log->save();
            \Illuminate\Support\Facades\Log::error('email send failed [' . $log->template_key . '] to ' . $log->to_email . ': ' . $e->getMessage());
            return false;
        }
    }

    /** @return [?string $email, ?string $name, ?int $userId] */
    protected function resolveRecipient($recipient): array
    {
        if ($recipient instanceof User) {
            return [$recipient->email, $recipient->name, $recipient->id];
        }
        if (is_string($recipient) && filter_var($recipient, FILTER_VALIDATE_EMAIL)) {
            return [$recipient, null, null];
        }
        if (is_array($recipient) && !empty($recipient['email'])) {
            return [$recipient['email'], $recipient['name'] ?? null, $recipient['user_id'] ?? null];
        }

        return [null, null, null];
    }

    protected function buildVars(?string $email, ?string $name, ?int $orderId, array $data): array
    {
        $vars = array_map(fn ($v) => is_scalar($v) || $v === null ? (string) $v : json_encode($v), $data);

        $vars['email'] = (string) $email;
        $vars['name'] = (string) ($name ?: ($vars['name'] ?? 'there'));

        if (!empty($orderId)) {
            $vars['order_id'] = (string) $orderId;
            try {
                $ref = DB::table('orders')->where('id', $orderId)->value('order_id');
                $vars['order_ref'] = (string) ($ref ?: ('#' . $orderId));
            } catch (\Throwable $e) {
                $vars['order_ref'] = '#' . $orderId;
            }
        }

        if (empty($vars['link']) && !empty($data['link'])) {
            $vars['link'] = (string) $data['link'];
        }

        return $vars;
    }

    /** Replace {{placeholder}} tokens (whitespace-tolerant, case-insensitive). */
    protected function render(string $text, array $vars): string
    {
        return preg_replace_callback('/\{\{\s*([a-zA-Z0-9_]+)\s*\}\}/', function ($m) use ($vars) {
            $key = strtolower($m[1]);
            return array_key_exists($key, $vars) ? (string) $vars[$key] : $m[0];
        }, $text) ?? $text;
    }
}
