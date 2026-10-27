<?php

namespace App\Listeners;

use App\Events\OrderPaymentRecorded;
use App\Services\ShopOrderService;
use Throwable;

/**
 * A pending order starts as soon as it is fully paid, whether the customer paid online or the shop
 * recorded the payment. A failure here is reported but never breaks the payment that triggered it.
 */
class StartOrderWhenPaid
{
    public function __construct(private readonly ShopOrderService $orders) {}

    public function handle(OrderPaymentRecorded $event): void
    {
        try {
            $this->orders->startWhenPaid($event->order);
        } catch (Throwable $exception) {
            report($exception);
        }
    }
}
