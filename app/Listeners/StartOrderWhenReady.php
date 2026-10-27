<?php

namespace App\Listeners;

use App\Events\OrderPaymentRecorded;
use App\Events\OrderWeightConfirmed;
use App\Services\ShopOrderService;
use Throwable;

/**
 * Starts a pending order as soon as it can start: when a payment completes it, or when its weight
 * makes the price final. A failure here is reported but never breaks the payment or weigh-in that
 * triggered it.
 */
class StartOrderWhenReady
{
    public function __construct(private readonly ShopOrderService $orders) {}

    public function handle(OrderPaymentRecorded|OrderWeightConfirmed $event): void
    {
        try {
            $this->orders->startWhenReady($event->order);
        } catch (Throwable $exception) {
            report($exception);
        }
    }
}
