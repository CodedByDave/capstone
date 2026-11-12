<?php

namespace App\Jobs;

use App\Repositories\ShopOrderRepository;
use App\Services\Sms\ShopSmsService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

/** Texts the customer that the laundry is ready, outside the request so a slow phone never delays staff. */
class SendOrderReadySms implements ShouldQueue
{
    use Queueable;

    /** The send is recorded either way; retrying could text the customer twice. */
    public int $tries = 1;

    public function __construct(public readonly int $orderId) {}

    public function handle(ShopOrderRepository $orders, ShopSmsService $sms): void
    {
        $order = $orders->findById($this->orderId);

        // Reopened or deleted before the job ran: nothing is ready any more.
        if ($order && $order->status === 'completed' && $sms->shouldAutoNotifyReady($order)) {
            $sms->notifyReady($order);
        }
    }
}
