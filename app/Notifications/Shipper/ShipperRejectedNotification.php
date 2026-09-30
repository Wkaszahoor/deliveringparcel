<?php

namespace App\Notifications\Shipper;

use App\Models\ShipperProfile;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ShipperRejectedNotification extends Notification
{
    use Queueable;

    public function __construct(private ShipperProfile $profile, private string $reason) {}

    public function via($notifiable)
    {
        if (in_array(app()->environment(), ['local', 'testing', 'dev'], true)) {
            return ['database'];
        }

        return ['mail', 'database'];
    }

    public function toMail($notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Shipper application update')
            ->line("Unfortunately your application was not approved: {$this->reason}");
    }

    public function toArray($notifiable): array
    {
        return [
            'type'   => 'rejected',
            'reason' => $this->reason,
            'title'  => 'Shipper application update',
            'body'   => "Your application was not approved: {$this->reason}",
        ];
    }
}
