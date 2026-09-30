<?php

namespace App\Notifications\Shipper;

use App\Models\ShipperOrderAssignment;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ShipperSelectedNotification extends Notification
{
    use Queueable;

    public function __construct(private ShipperOrderAssignment $assignment) {}

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
            ->subject("You have been selected — Order #{$this->assignment->order_id}")
            ->line('Congratulations! You have been selected for a shipping assignment.')
            ->action('View Assignment', url('/shipper/assignments/' . $this->assignment->id));
    }

    public function toArray($notifiable): array
    {
        return [
            'type'          => 'selected',
            'assignment_id' => $this->assignment->id,
            'order_id'      => $this->assignment->order_id,
            'title'         => 'You were selected for order #' . $this->assignment->order_id,
            'body'          => 'Congratulations! Check your assignments for details.',
        ];
    }
}
