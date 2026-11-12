<?php

namespace App\Repositories;

use App\Models\SmsMessage;

class SmsMessageRepository
{
    public function create(array $data): SmsMessage
    {
        return SmsMessage::create($data);
    }

    /** The latest "ready for pickup" text for an order, with who sent it. */
    public function latestReadyForOrder(int $orderId): ?SmsMessage
    {
        return SmsMessage::query()
            ->where('shop_order_id', $orderId)
            ->where('purpose', SmsMessage::PURPOSE_ORDER_READY)
            ->with('sender:id,name')
            ->latest('id')
            ->first();
    }
}
