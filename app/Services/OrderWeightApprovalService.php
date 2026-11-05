<?php

namespace App\Services;

use App\Events\OrderWeightConfirmed;
use App\Models\ShopOrder;
use App\Models\User;
use App\Notifications\OrderNotification;
use App\Repositories\ShopOrderRepository;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/** The customer's decision on the weight a shop recorded for an online order. */
class OrderWeightApprovalService
{
    public function __construct(
        private readonly ShopOrderRepository $orders,
        private readonly ShopOrderService $orderService,
        private readonly ShopOrderPaymentService $onlinePayments,
    ) {}

    public function approve(User $customer, ShopOrder $order): ShopOrder
    {
        $updated = DB::transaction(function () use ($customer, $order) {
            $locked = $this->lockPending($customer, $order);

            return $this->orders->update($locked, [
                'weight_approval_status' => 'approved',
                'weight_decided_at' => now(),
            ]);
        });

        // The weight is final now: the order starts, and the shop's automation rules run, as after a weigh-in.
        OrderWeightConfirmed::dispatch($updated);
        $this->notifyShop($updated->fresh(['shop']));

        return $updated->fresh();
    }

    public function decline(User $customer, ShopOrder $order): ShopOrder
    {
        // Look at any open online checkout first so a payment already made is not lost.
        $this->onlinePayments->cancelPending($customer, $order->payment_public_id);

        $updated = DB::transaction(function () use ($customer, $order) {
            $locked = $this->lockPending($customer, $order);

            $this->orders->update($locked, [
                'weight_approval_status' => 'declined',
                'weight_decided_at' => now(),
            ]);

            return $this->orderService->updateStatus($locked->refresh(), 'cancelled');
        });

        $this->notifyShop($updated->fresh(['shop']));

        return $updated;
    }

    /** Proof photo path for the order's own customer; anyone else gets the same not-found. */
    public function proofPathForCustomer(User $customer, ShopOrder $order): string
    {
        if ($order->user_id !== $customer->id) {
            throw (new ModelNotFoundException)->setModel(ShopOrder::class);
        }

        return $this->proofPath($order);
    }

    public function proofPath(ShopOrder $order): string
    {
        if (! $order->weight_proof_path) {
            throw (new ModelNotFoundException)->setModel(ShopOrder::class);
        }

        return $order->weight_proof_path;
    }

    private function lockPending(User $customer, ShopOrder $order): ShopOrder
    {
        if ($order->user_id !== $customer->id) {
            throw (new ModelNotFoundException)->setModel(ShopOrder::class);
        }

        $locked = $this->orders->lockForUpdate($order);

        if ($locked->order_source !== 'online'
            || $locked->status !== 'pending'
            || $locked->weight_approval_status !== 'pending') {
            throw ValidationException::withMessages([
                'weight' => 'This order has no recorded weight waiting for your approval.',
            ]);
        }

        return $locked;
    }

    private function notifyShop(ShopOrder $order): void
    {
        $order->shop?->owner?->notify(new OrderNotification($order, 'weight_decided'));
    }
}
