<?php

namespace App\Notifications;

use App\Models\Delivery;
use Illuminate\Notifications\Notification;

/** Tells a driver a run was just assigned to them, so they can accept or reject it. */
class DriverAssignmentNotification extends Notification
{
    public function __construct(public readonly Delivery $delivery) {}

    public function via(object $notifiable): array
    {
        return ['database'];
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
}
