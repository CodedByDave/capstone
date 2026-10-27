<?php

namespace App\Notifications;

use App\Models\ShopOrder;
use Illuminate\Notifications\Notification;

class OrderNotification extends Notification
{
    public function __construct(
        public readonly ShopOrder $order,
        public readonly string $event, // placed | weight_confirmed | status_changed | payment_updated | cancelled | overdue
        // The payment just received, so the message can say how much and what is left.
        public readonly ?float $amount = null,
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
                'title' => 'Order placed!',
                'body' => "Your order #{$order->order_number} at {$order->shop->shop_name} has been received. We'll notify you when it's in progress.",
                'order_id' => $order->id,
                'payment_public_id' => $order->payment_public_id,
                'order_number' => $order->order_number,
                'type' => 'placed',
            ],
            'status_changed' => [
                'title' => $this->statusTitle($order->status),
                'body' => $this->statusBody($order),
                'order_id' => $order->id,
                'payment_public_id' => $order->payment_public_id,
                'order_number' => $order->order_number,
                'type' => 'status_changed',
                'status' => $order->status,
            ],
            'weight_confirmed' => [
                'title' => 'Final weight confirmed',
                'body' => 'Your laundry weighs '.number_format((float) $order->actual_weight_kg, 2)
                    .' kg. The final amount for order #'.$order->order_number.' is ₱'
                    .number_format((float) $order->total_amount, 2).'. You can now pay online.',
                'order_id' => $order->id,
                'payment_public_id' => $order->payment_public_id,
                'order_number' => $order->order_number,
                'type' => 'weight_confirmed',
                'actual_weight_kg' => $order->actual_weight_kg,
                'amount' => $order->total_amount,
            ],
            'payment_updated' => [
                'title' => $this->paymentTitle($order),
                'body' => $this->paymentBody($order),
                'order_id' => $order->id,
                'payment_public_id' => $order->payment_public_id,
                'order_number' => $order->order_number,
                'type' => 'payment_updated',
                'payment_status' => $order->payment_status,
            ],
            'cancelled' => [
                'title' => 'Order cancelled',
                'body' => "Order #{$order->order_number} has been cancelled by the customer.",
                'order_id' => $order->id,
                'payment_public_id' => $order->payment_public_id,
                'order_number' => $order->order_number,
                'type' => 'cancelled',
            ],
            'overdue' => [
                'title' => 'Order is overdue',
                'body' => "Order #{$order->order_number} is past its expected completion time and is still in progress.",
                'order_id' => $order->id,
                'payment_public_id' => $order->payment_public_id,
                'order_number' => $order->order_number,
                'type' => 'overdue',
                'url' => '/shop/operations/orders/'.$order->payment_public_id,
            ],
            default => [],
        };
    }

    private function paymentTitle(ShopOrder $order): string
    {
        return match ($order->payment_status) {
            'paid' => 'Order fully paid',
            'partial' => 'Payment received',
            default => 'Payment updated',
        };
    }

    private function paymentBody(ShopOrder $order): string
    {
        $received = $this->amount !== null && $this->amount > 0
            ? '₱'.number_format($this->amount, 2).' received for'
            : 'Payment recorded for';

        return match ($order->payment_status) {
            'paid' => "{$received} order #{$order->order_number}. It is now fully paid.",
            'partial' => "{$received} order #{$order->order_number}. Remaining balance: ₱".number_format($order->balanceDue(), 2).'.',
            default => "Payment for order #{$order->order_number} is now {$order->payment_status}.",
        };
    }

    private function statusTitle(string $status): string
    {
        return match ($status) {
            'in_progress' => 'Laundry in progress 🧺',
            'completed' => 'Order completed ✅',
            'picked_up' => 'Order picked up 📦',
            default => 'Order updated',
        };
    }

    private function statusBody(ShopOrder $order): string
    {
        return match ($order->status) {
            'in_progress' => "Your laundry at {$order->shop->shop_name} is now being washed. Order #{$order->order_number}.",
            'completed' => "Your laundry is ready for pickup! Order #{$order->order_number} at {$order->shop->shop_name}.".($order->pickup_type === 'pickup' || $order->deliver_to_customer ? '' : ' How did we do? Rate your order.'),
            'picked_up' => "Order #{$order->order_number} was picked up from {$order->shop->shop_name}. Thank you! How did we do? Rate your order.",
            default => "Order #{$order->order_number} status updated to {$order->status}.",
        };
    }
}
