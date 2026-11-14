<?php

namespace App\Notifications;

use App\Models\Delivery;
use Illuminate\Notifications\Notification;

/**
 * Tells the shop that a run was not given to a rider because the address is beyond the
 * delivery radius the shop set, so a wrong setting is noticed straight away.
 */
class DeliveryHeldNotification extends Notification
{
    public function __construct(
        public readonly Delivery $delivery,
        public readonly string $reason,
    ) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toDatabase(object $notifiable): array
    {
        $order = $this->delivery->order;
        $ref = $order ? "order #{$order->order_number}" : 'an order';

        return [
            'type' => 'delivery_held',
            'title' => 'Delivery held: not given to a rider',
            'body' => "{$ref} was held. {$this->reason}. Assign a rider yourself, or change the radius in Settings if it is too small.",
            'delivery_id' => $this->delivery->id,
            'order_id' => $order?->id,
            'order_number' => $order?->order_number,
            'url' => '/shop/logistics',
        ];
    }
}
