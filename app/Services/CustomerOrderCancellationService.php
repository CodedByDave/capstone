<?php

namespace App\Services;

use App\Models\ShopOrder;
use App\Models\User;
use App\Notifications\OrderNotification;
use App\Repositories\ShopOrderRepository;
use Illuminate\Support\Facades\Notification;
use Illuminate\Validation\ValidationException;

/**
 * A customer cancelling their own order while the shop has not started on it. The cancellation
 * goes through the order service, the same as when the shop cancels: add-ons, products and the
 * service's supplies go back in stock and any payment is booked as a refund.
 */
class CustomerOrderCancellationService
{
    public function __construct(
        private readonly ShopOrderRepository $orders,
        private readonly ShopOrderService $orderService,
        private readonly ShopOrderPaymentService $onlinePayments,
    ) {}

    /**
     * @throws ValidationException when the order can no longer be cancelled
     */
    public function cancel(User $customer, ShopOrder $order): ShopOrder
    {
        if ($order->status !== 'pending') {
            throw ValidationException::withMessages(['status' => 'Only pending orders can be cancelled.']);
        }

        // Look at any open online checkout first so a payment already made is not lost.
        $this->onlinePayments->cancelPending($customer, $order->payment_public_id);

        $cancelled = $this->orderService->updateStatus($order->refresh(), 'cancelled');
        $cancelled->loadMissing('shop');

        if ($cancelled->shop) {
            Notification::send(
                $this->orders->onlineOrderRecipients($cancelled->shop),
                new OrderNotification($cancelled, 'cancelled'),
            );
        }

        return $cancelled;
    }
}
