<?php

namespace App\Notifications;

use App\Models\Delivery;
use Illuminate\Notifications\Notification;

/**
 * An order needs a rider but none can take it right now. The customer is told it is waiting; the
 * shop is told to act.
 */
class NoRiderAvailableNotification extends Notification
{
    public const FOR_CUSTOMER = 'customer';

    public const FOR_SHOP = 'shop';

    public function __construct(
        public readonly Delivery $delivery,
        public readonly string $audience = self::FOR_CUSTOMER,
    ) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toDatabase(object $notifiable): array
    {
        $order = $this->delivery->order;
        $ref = $order ? "order #{$order->order_number}" : 'the order';
        $forCustomer = $this->audience === self::FOR_CUSTOMER;

        return [
            'type' => 'no_rider_available',
            'title' => 'No rider available',
            'body' => $forCustomer
                ? "There is no rider available for {$ref} right now. The shop will assign one as soon as a rider is free."
                : "No rider is available for {$ref}. It is waiting in Logistics until a rider is free, or you can assign one yourself.",
            'delivery_id' => $this->delivery->id,
            'order_id' => $order?->id,
            'payment_public_id' => $order?->payment_public_id,
            'order_number' => $order?->order_number,
            'url' => $forCustomer ? null : '/shop/logistics',
        ];
    }
}
