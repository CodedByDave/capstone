<?php

namespace App\Notifications;

use App\Models\Delivery;
use App\Models\DeliveryRejection;
use Illuminate\Notifications\Notification;

/** Tells the shop a driver turned down a run, with the reason they gave. */
class DeliveryRejectedNotification extends Notification
{
    public function __construct(
        public readonly Delivery $delivery,
        public readonly DeliveryRejection $rejection,
        public readonly string $riderName,
    ) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toDatabase(object $notifiable): array
    {
        $number = $this->delivery->order?->order_number;
        $ref = $number ? "order #{$number}" : 'an order';
        $note = $this->rejection->note ? " \"{$this->rejection->note}\"" : '';

        return [
            'type' => 'delivery_rejected',
            'title' => 'A driver rejected an order',
            'body' => "{$this->riderName} rejected {$ref}: {$this->rejection->reasonLabel()}.{$note} It goes to the next available rider.",
            'delivery_id' => $this->delivery->id,
            'order_number' => $number,
            // Opens the photo the driver gave as proof of their reason.
            'url' => '/shop/logistics/rejections/'.$this->rejection->public_id.'/photo',
        ];
    }
}
