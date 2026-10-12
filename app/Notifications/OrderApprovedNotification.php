<?php

namespace App\Notifications;

use App\Models\Order;
use Illuminate\Notifications\Notification;

class OrderApprovedNotification extends Notification
{
    public function __construct(
        public readonly Order $order,
    ) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toDatabase(object $notifiable): array
    {
        return [
            'type' => 'order_approved',
            'title' => 'Order approved',
            'body' => "Your {$this->order->plan_name} plan order for {$this->order->shop_name} has been approved. You can now continue to payment. Your fully signed agreement is also ready in Legal Documents.",
            'order_public_id' => $this->order->public_id,
            'url' => route('shop.dashboard'),
        ];
    }
}
