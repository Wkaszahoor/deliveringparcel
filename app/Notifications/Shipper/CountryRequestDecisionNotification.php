<?php

namespace App\Notifications\Shipper;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class CountryRequestDecisionNotification extends Notification
{
    use Queueable;

    public function __construct(private string $decision, private array $countries, private string $adminNote = '')
    {
    }

    public function via($notifiable)
    {
        if (in_array(app()->environment(), ['local', 'testing', 'dev'], true)) {
            return ['database'];
        }

        return ['mail', 'database'];
    }

    public function toMail($notifiable): MailMessage
    {
        $mail = (new MailMessage)
            ->subject($this->decision === 'approved'
                ? 'Your service countries update was approved'
                : 'Your service countries update was rejected')
            ->line($this->decision === 'approved'
                ? 'Your service countries have been updated to: ' . implode(', ', $this->countries)
                : 'Your service countries request was not approved.');

        if ($this->decision !== 'approved' && $this->adminNote !== '') {
            $mail->line('Admin note: ' . $this->adminNote);
        }

        return $mail->action('Open Shipper Workspace', url('/shipper/countries'));
    }

    public function toArray($notifiable): array
    {
        return [
            'type'  => 'countries_' . $this->decision,
            'title' => $this->decision === 'approved'
                ? 'Service countries updated'
                : 'Service countries request rejected',
            'body'  => $this->decision === 'approved'
                ? 'Your service countries are now: ' . implode(', ', $this->countries)
                : ($this->adminNote !== '' ? $this->adminNote : 'Your countries change request was not approved.'),
        ];
    }
}
