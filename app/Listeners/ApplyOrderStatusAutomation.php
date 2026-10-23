<?php

namespace App\Listeners;

use App\Events\OrderPaymentRecorded;
use App\Events\OrderWeightConfirmed;
use App\Services\OrderStatusAutomationService;
use Throwable;

/**
 * Lets a shop's automation rules react to what just happened on an order. A failure here is
 * reported but never breaks the weigh-in or payment that triggered it.
 */
class ApplyOrderStatusAutomation
{
    public function __construct(private readonly OrderStatusAutomationService $automation) {}

    public function onWeightConfirmed(OrderWeightConfirmed $event): void
    {
        try {
            $this->automation->onWeightConfirmed($event->order);
        } catch (Throwable $exception) {
            report($exception);
        }
    }

    public function onPaymentRecorded(OrderPaymentRecorded $event): void
    {
        try {
            $this->automation->onPaymentRecorded($event->order);
        } catch (Throwable $exception) {
            report($exception);
        }
    }
}
