<?php

namespace App\Notifications;

use App\Models\Delivery;
use Illuminate\Notifications\Notification;

class DeliveryNotification extends Notification
{
    public function __construct(
        public readonly Delivery $delivery,
    ) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toDatabase(object $notifiable): array
    {
        $delivery = $this->delivery;
        $order    = $delivery->order;

        return [
            'title'           => $this->statusTitle($delivery->status),
            'body'            => $this->statusBody($delivery),
            'type'            => 'delivery_updated',
            'delivery_id'     => $delivery->id,
            'order_id'        => $order?->id,
            'order_number'    => $order?->order_number,
            'delivery_status' => $delivery->status,
        ];
    }

    private function statusTitle(string $status): string
    {
        return match ($status) {
            'assigned'  => 'Rider assigned',
            'picked_up' => 'Order picked up',
            'delivered' => 'Order delivered!',
            'failed'    => 'Delivery failed',
            default     => 'Delivery update',
        };
    }

    private function statusBody(Delivery $delivery): string
    {
        $order  = $delivery->order;
        $ref    = $order ? "Order #{$order->order_number}" : 'Your order';
        $rider  = $delivery->rider?->name ?? 'our rider';

        return match ($delivery->status) {
            'assigned'  => "{$ref} has been assigned to {$rider} and will be picked up shortly.",
            'picked_up' => "{$ref} has been picked up by {$rider} and is on the way to you.",
            'delivered' => "{$ref} has been delivered. Thank you for choosing us!",
            'failed'    => "{$ref} could not be delivered. Please contact the shop for assistance.",
            default     => "{$ref} delivery status has been updated to {$delivery->status}.",
        };
    }
}
