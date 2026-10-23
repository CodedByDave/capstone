<?php

namespace App\Notifications;

use App\Models\OrderComplaint;
use Illuminate\Notifications\Notification;

class OrderComplaintNotification extends Notification
{
    public function __construct(
        public readonly OrderComplaint $complaint,
        public readonly string $event, // filed (to the shop) | responded (to the customer)
    ) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toDatabase(object $notifiable): array
    {
        $complaint = $this->complaint;
        $order = $complaint->order;
        $type = OrderComplaint::TYPES[$complaint->type] ?? 'Problem';

        if ($this->event === 'filed') {
            return [
                'title' => 'New customer complaint',
                'body' => "{$order->customer_name} reported a problem ({$type}) with order #{$order->order_number}.",
                'type' => 'complaint_filed',
                'order_number' => $order->order_number,
                'url' => '/shop/operations/complaints',
            ];
        }

        return [
            'title' => match ($complaint->status) {
                'resolved' => 'Your report was resolved',
                'rejected' => 'Your report was closed',
                default => 'Your report is being reviewed',
            },
            'body' => "{$order->shop->shop_name} updated your report about order #{$order->order_number}.",
            'type' => 'complaint_updated',
            'order_id' => $order->id,
            'payment_public_id' => $order->payment_public_id,
            'order_number' => $order->order_number,
            'status' => $complaint->status,
        ];
    }
}
