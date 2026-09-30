<?php

namespace App\Notifications\Shipper;

use App\Models\ShipperProfile;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ShipperApprovedNotification extends Notification
{
    use Queueable;

    public function __construct(private ShipperProfile $profile) {}

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
            ->subject('Your shipper account has been approved!')
            ->line('Welcome to the DeliveringParcel shipper network.')
            ->action('Open Dashboard', url('/shipper/dashboard'));
    }

    public function toArray($notifiable): array
    {
        return [
            'type'  => 'approved',
            'title' => 'Shipper account approved',
            'body'  => 'Welcome to the DeliveringParcel shipper network. Your account is now active.',
        ];
    }
}
