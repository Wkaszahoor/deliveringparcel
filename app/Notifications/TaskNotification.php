<?php

namespace App\Notifications;

use App\Services\EmailService;
use App\Support\PushNotificationService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Http\Request;

class TaskNotification extends Notification
{


    private $details;
    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct($details)
    {
        $this->details = $details;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        // Local/test environments must never attempt SMTP delivery: seed
        // users use @dptest.local domains that real mail servers reject
        // (503), which crashed order placement with Swift_TransportException
        // AFTER the order was already persisted. Production keeps mail.
        if (in_array(app()->environment(), ['local', 'testing', 'dev'], true)) {
            return ['database'];
        }

        return ['mail', 'database'];
    }

    /**
     * Send the notification — fires EmailService email first, then
     * dispatches to the database channel via parent.
     *
     * We override this instead of relying on toMail() so that emails
     * go through EmailService (branded template, logging, queue) rather
     * than the stock Laravel MailMessage body.
     */
    public function send($notifiable, $channels)
    {
        // Always attempt our branded email — EmailService itself checks
        // the master switch, per-template toggle, and environment.
        $this->sendEmail($notifiable);

        // Parent dispatches to the channels returned by via() (database).
        // We remove 'mail' from channels to prevent Laravel sending its
        // own stock MailMessage — our email is already handled above.
        $dbOnly = array_values(array_diff($channels, ['mail']));
        parent::send($notifiable, $dbOnly);

        // Fire-and-forget push notification to registered mobile devices.
        // Never crashes the notification pipeline — failures are logged.
        $this->sendPush($notifiable);
    }

    /**
     * Fire-and-forget EmailService email. Contract: never throws.
     */
    protected function sendEmail($notifiable): void
    {
        try {
            $orderId = $this->details['order_id'] ?? null;
            $link    = $this->details['link'] ?? url('/');

            // If there's an order, build a deep link to the order page.
            if ($orderId && empty($this->details['link'])) {
                $link = url('/orders/' . $orderId);
            }

            $data = [
                'title'       => $this->details['title'] ?? '',
                'greeting'    => $this->details['greeting'] ?? '',
                'description' => $this->details['description'] ?? '',
                'link'        => $link,
            ];

            app(EmailService::class)->send(
                'notification_task',
                $notifiable,
                $data,
                $orderId
            );
        } catch (\Throwable $e) {
            \Log::error('TaskNotification email failed for user #' . ($notifiable->id ?? '?') . ': ' . $e->getMessage());
        }
    }

    /**
     * Get the mail representation of the notification.
     *
     * NOTE: This is dead code — via() no longer includes 'mail'. Kept as
     * a safety net if someone adds 'mail' back to via() in the future.
     *
     * @param  mixed  $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable)
    {
        return (new MailMessage)
                    ->line($this->details['title'])
                    ->action($this->details['greeting'], url('/'))
                    ->line($this->details['description'])
                    ->line('Thank you for using our application!');
    }

    /**
     * Fire-and-forget push notification via FCM. Contract: never throws.
     */
    protected function sendPush($notifiable): void
    {
        try {
            $orderId = $this->details['order_id'] ?? null;
            $title   = $this->details['title'] ?? 'DeliveringParcel';
            $body    = $this->details['description'] ?? $this->details['greeting'] ?? '';

            $data = [];
            if ($orderId) {
                $data['order_id'] = (string) $orderId;
            }

            PushNotificationService::sendToUser($notifiable, $title, $body, $data);
        } catch (\Throwable $e) {
            \Log::error('TaskNotification push failed for user #' . ($notifiable->id ?? '?') . ': ' . $e->getMessage());
        }
    }

    /**
     * Get the array representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function toDatabase($notifiable)
    {
        return $this->details;
    }
}
