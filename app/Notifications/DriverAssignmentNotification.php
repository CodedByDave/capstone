<?php

namespace App\Notifications;

use App\Models\Delivery;
use Illuminate\Notifications\Notification;
use NotificationChannels\WebPush\WebPushChannel;
use NotificationChannels\WebPush\WebPushMessage;

/** Tells a driver a run was just assigned to them, so they can accept or reject it. */
class DriverAssignmentNotification extends Notification
{
    public function __construct(public readonly Delivery $delivery) {}

    public function via(object $notifiable): array
    {
        // Web push reaches the phone as a system notification even when the app is closed.
        return ['database', WebPushChannel::class];
    }

    public function toDatabase(object $notifiable): array
    {
        $delivery = $this->delivery;
        $number = $delivery->order?->order_number;
        $ref = $number ? "order #{$number}" : 'an order';
        $what = $delivery->isPickupRun() ? "collect the laundry for {$ref} from the customer" : "deliver {$ref} to the customer";

        return [
            'type' => 'driver_assignment',
            'title' => 'New order assigned to you',
            'body' => "You were assigned to {$what}. Open it to accept or reject.",
            'delivery_public_id' => $delivery->public_id,
            'order_number' => $number,
            'url' => '/driver/jobs/'.$delivery->public_id,
        ];
    }

    public function toWebPush(object $notifiable): WebPushMessage
    {
        $data = $this->toDatabase($notifiable);

        return (new WebPushMessage)
            ->title($data['title'])
            ->body($data['body'])
            ->icon('/pwa-192x192.png')
            ->badge('/pwa-64x64.png')
            ->tag('driver-job-'.$data['delivery_public_id'])
            ->vibrate([300, 150, 300, 150, 300])
            ->requireInteraction()
            ->data(['url' => $data['url']]);
    }
}
