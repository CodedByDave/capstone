<?php

namespace App\Notifications;

use App\Models\ShopOrder;
use Illuminate\Notifications\Notification;

class OrderNotification extends Notification
{
    public function __construct(
        public readonly ShopOrder $order,
        public readonly string    $event, // placed | status_changed | payment_updated | payment_request
        public readonly ?string   $qrUrl  = null,
    ) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toDatabase(object $notifiable): array
    {
        $order = $this->order;

        return match ($this->event) {
            'placed' => [
                'title'        => 'Order placed!',
                'body'         => "Your order #{$order->order_number} at {$order->shop->shop_name} has been received. We'll notify you when it's in progress.",
                'order_id'     => $order->id,
                'order_number' => $order->order_number,
                'type'         => 'placed',
            ],
            'status_changed' => [
                'title'        => $this->statusTitle($order->status),
                'body'         => $this->statusBody($order),
                'order_id'     => $order->id,
                'order_number' => $order->order_number,
                'type'         => 'status_changed',
                'status'       => $order->status,
            ],
            'payment_updated' => [
                'title'        => 'Payment updated',
                'body'         => "Payment for order #{$order->order_number} is now {$order->payment_status}.",
                'order_id'     => $order->id,
                'order_number' => $order->order_number,
                'type'         => 'payment_updated',
                'payment_status' => $order->payment_status,
            ],
            'payment_request' => [
                'title'        => 'Payment request from ' . $order->shop->shop_name,
                'body'         => "Please pay ₱" . number_format($order->total_amount, 2) . " for order #{$order->order_number} via " . strtoupper($order->payment_method) . ".",
                'order_id'     => $order->id,
                'order_number' => $order->order_number,
                'type'         => 'payment_request',
                'amount'       => $order->total_amount,
                'qr_url'       => $this->qrUrl,
            ],
            default => [],
        };
    }

    private function statusTitle(string $status): string
    {
        return match ($status) {
            'in_progress' => 'Laundry in progress 🧺',
            'completed'   => 'Order completed ✅',
            default       => 'Order updated',
        };
    }

    private function statusBody(ShopOrder $order): string
    {
        return match ($order->status) {
            'in_progress' => "Your laundry at {$order->shop->shop_name} is now being washed. Order #{$order->order_number}.",
            'completed'   => "Your laundry is ready for pickup! Order #{$order->order_number} at {$order->shop->shop_name}.",
            default       => "Order #{$order->order_number} status updated to {$order->status}.",
        };
    }
}
