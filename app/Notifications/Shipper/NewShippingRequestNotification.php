<?php

namespace App\Notifications\Shipper;

use App\Models\ShippingRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class NewShippingRequestNotification extends Notification
{
    use Queueable;

    public function __construct(private ShippingRequest $request) {}

    public function via($notifiable)
    {
        // Local/test never attempts SMTP (same rule as TaskNotification).
        if (in_array(app()->environment(), ['local', 'testing', 'dev'], true)) {
            return ['database'];
        }

        return ['mail', 'database'];
    }

    public function toMail($notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject("New Shipping Request Available — {$this->request->reference}")
            ->line("A new {$this->request->service_type} request is available in your country ({$this->request->country_required}).")
            ->action('View Request', url('/shipper/requests/' . $this->request->id));
    }

    public function toArray($notifiable): array
    {
        return [
            'type'       => 'new_request',
            'request_id' => $this->request->id,
            'reference'  => $this->request->reference,
            'country'    => $this->request->country_required,
            'title'      => 'New shipping request ' . $this->request->reference,
            'body'       => "A new {$this->request->service_type} request is available in {$this->request->country_required}.",
        ];
    }
}
