<?php

namespace App\Notifications;

use App\Models\PurchaseOrder;
use Illuminate\Notifications\Notification;

/** Keeps a shop and its supplier in step on a purchase order: sent, confirmed, declined, cancelled. */
class PurchaseOrderNotification extends Notification
{
    public function __construct(
        public readonly PurchaseOrder $order,
        public readonly string $event,
        public readonly string $url,
    ) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toDatabase(object $notifiable): array
    {
        $order = $this->order->loadMissing(['shop:id,shop_name', 'supplier:id,name']);
        $number = $order->po_number;
        $date = $order->expected_date?->format('M j, Y');

        [$title, $body] = match ($this->event) {
            'sent' => ['New purchase order', "{$order->shop?->shop_name} sent you purchase order {$number}."],
            'cancelled' => ['Purchase order cancelled', "{$order->shop?->shop_name} cancelled purchase order {$number}."],
            'confirmed' => ['Supplier confirmed your order', "{$order->supplier?->name} confirmed {$number}".($date ? ", for delivery on {$date}." : '.')],
            'declined' => ['Supplier declined your order', "{$order->supplier?->name} declined {$number}".($order->supplier_note ? ": \"{$order->supplier_note}\"" : '.')],
            default => ['Purchase order updated', "Purchase order {$number} was updated."],
        };

        return [
            'type' => 'purchase_order_'.$this->event,
            'title' => $title,
            'body' => $body,
            'po_number' => $number,
            'url' => $this->url,
        ];
    }
}
