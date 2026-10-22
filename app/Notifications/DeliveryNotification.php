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
        $order = $delivery->order;

        return [
            'title' => $this->statusTitle($delivery),
            'body' => $this->statusBody($delivery),
            'type' => 'delivery_updated',
            'delivery_id' => $delivery->id,
            'order_id' => $order?->id,
            'payment_public_id' => $order?->payment_public_id,
            'order_number' => $order?->order_number,
            'delivery_status' => $delivery->status,
        ];
    }

    private function statusTitle(Delivery $delivery): string
    {
        if ($delivery->isPickupRun()) {
            return match ($delivery->status) {
                'assigned' => 'Rider on the way to collect',
                'picked_up' => 'Laundry collected',
                'delivered' => 'Laundry received at the shop',
                'failed' => 'Pickup failed',
                default => 'Pickup update',
            };
        }

        return match ($delivery->status) {
            'assigned' => 'Rider assigned',
            'picked_up' => 'Order picked up',
            'delivered' => 'Order delivered!',
            'failed' => 'Delivery failed',
            default => 'Delivery update',
        };
    }

    private function statusBody(Delivery $delivery): string
    {
        $order = $delivery->order;
        $ref = $order ? "Order #{$order->order_number}" : 'Your order';
        $rider = $delivery->usesLalamove()
            ? ($delivery->courier_driver_name ?? 'a Lalamove driver')
            : ($delivery->rider?->name ?? 'our rider');

        if ($delivery->isPickupRun()) {
            return match ($delivery->status) {
                'assigned' => "{$rider} will collect the laundry for {$ref} from your address.",
                'picked_up' => "{$rider} has collected the laundry for {$ref} and is heading to the shop.",
                'delivered' => "The laundry for {$ref} has arrived at the shop.",
                'failed' => "We could not collect the laundry for {$ref}. Please contact the shop for assistance.",
                default => "The pickup for {$ref} has been updated to {$delivery->status}.",
            };
        }

        return match ($delivery->status) {
            'assigned' => "{$ref} has been assigned to {$rider} and will be picked up shortly.",
            'picked_up' => "{$ref} has been picked up by {$rider} and is on the way to you.",
            'delivered' => "{$ref} has been delivered. Thank you for choosing us!",
            'failed' => "{$ref} could not be delivered. Please contact the shop for assistance.",
            default => "{$ref} delivery status has been updated to {$delivery->status}.",
        };
    }
}
