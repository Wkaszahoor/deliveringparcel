<?php

namespace App\Notifications;

use App\Services\EmailService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Http\Request;

class Chatnotification extends Notification
{


    private $data;
    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct($data)
    {
        $this->data = $data;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        // Same rule as TaskNotification: never SMTP seed @dptest.local
        // addresses from local/test environments (mail server replies 503).
        if (in_array(app()->environment(), ['local', 'testing', 'dev'], true)) {
            return ['database'];
        }
        return ['mail', 'database'];
    }

    /**
     * Send the notification — fires EmailService email first, then
     * dispatches to the database channel via parent.
     */
    public function send($notifiable, $channels)
    {
        $this->sendEmail($notifiable);

        // Strip 'mail' to prevent Laravel's stock MailMessage.
        $dbOnly = array_values(array_diff($channels, ['mail']));
        parent::send($notifiable, $dbOnly);
    }

    /**
     * Fire-and-forget EmailService email. Contract: never throws.
     */
    protected function sendEmail($notifiable): void
    {
        try {
            $orderId = $this->data['id'] ?? null;
            $link    = $this->data['link'] ?? url('/');

            if ($orderId && empty($this->data['link'])) {
                $link = url('/orders/' . $orderId);
            }

            $data = [
                'title'       => $this->data['title'] ?? '',
                'greeting'    => $this->data['greeting'] ?? '',
                'body'        => $this->data['body'] ?? '',
                'description' => $this->data['description'] ?? '',
                'link'        => $link,
            ];

            app(EmailService::class)->send(
                'notification_chat',
                $notifiable,
                $data,
                $orderId
            );
        } catch (\Throwable $e) {
            \Log::error('Chatnotification email failed for user #' . ($notifiable->id ?? '?') . ': ' . $e->getMessage());
        }
    }

    /**
     * Get the mail representation of the notification.
     *
     * NOTE: Dead code — send() strips 'mail' from channels. Kept as
     * a safety net if someone reverts the override.
     *
     * @param  mixed  $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable)
    {
        return (new MailMessage)
                    ->line($this->data['title'])
                    ->action($this->data['body'], url('/'))
                    ->line('Thank you for using our application!');
    }

    /**
     * Get the array representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function toArray($notifiable)
    {
        return $this->data;
    }
}
