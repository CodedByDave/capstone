<?php

namespace App\Services;

use App\Models\ShopOrder;
use App\Models\ShopOrderPayment;
use App\Models\User;
use App\Notifications\OrderNotification;
use App\Repositories\OrderPaymentRepository;
use App\Repositories\ShopOrderRepository;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

/**
 * Payments toward a shop order. The payments are the record; the order's paid total, status and
 * paid date are recalculated from the confirmed ones every time they change.
 */
class OrderPaymentService
{
    public function __construct(
        private readonly OrderPaymentRepository $payments,
        private readonly ShopOrderRepository $orders,
        private readonly ShopContext $shopContext,
    ) {}

    public function history(ShopOrder $order): Collection
    {
        return $this->payments->historyForOrder($order->id);
    }

    /**
     * What the customer sees of their order's payments: the money received and any online
     * checkout still waiting to be confirmed.
     *
     * @return array{payments: list<array<string, mixed>>, pending: ?array<string, mixed>}
     */
    public function customerView(ShopOrder $order): array
    {
        $pending = $this->payments->latestPendingForOrder($order->id);

        return [
            'payments' => $this->payments->confirmedForOrder($order->id)->map(fn (ShopOrderPayment $payment) => [
                'id' => $payment->public_id,
                'amount' => (float) $payment->amount,
                'method' => $payment->method,
                'paid_at' => ($payment->paid_at ?? $payment->created_at)->toIso8601String(),
            ])->values()->all(),
            'pending' => $pending ? [
                'amount' => (float) $pending->amount,
                'method' => $pending->method,
                'started_at' => $pending->created_at->toIso8601String(),
            ] : null,
        ];
    }

    /**
     * Record money received at the shop, a part of the balance or all of it.
     *
     * @param  array{amount: numeric, method: string, reference?: ?string, note?: ?string}  $data
     */
    public function recordShopPayment(User $actor, ShopOrder $order, array $data): ShopOrderPayment
    {
        $this->assertOrderBelongsToActorShop($actor, $order);

        [$payment, $locked] = DB::transaction(function () use ($actor, $order, $data) {
            $locked = $this->orders->lockForUpdate($order);

            $payment = $this->addConfirmed($locked, [
                'amount' => $data['amount'],
                'method' => $data['method'],
                'reference' => $data['reference'] ?? null,
                'note' => $data['note'] ?? null,
                'source' => ShopOrderPayment::SOURCE_SHOP,
                'recorded_by' => $actor->id,
            ]);

            return [$payment, $locked];
        });

        $this->notifyCustomer($locked, (float) $payment->amount);

        return $payment;
    }

    /** Take back a wrongly recorded payment; it stays in the history with the reason. */
    public function voidPayment(User $actor, ShopOrder $order, string $paymentPublicId, string $reason): ShopOrderPayment
    {
        $this->assertOrderBelongsToActorShop($actor, $order);

        return DB::transaction(function () use ($actor, $order, $paymentPublicId, $reason) {
            $locked = $this->orders->lockForUpdate($order);

            $payment = $this->payments->findForOrder($locked->id, $paymentPublicId)
                ?? throw (new ModelNotFoundException)->setModel(ShopOrderPayment::class);
            $payment = $this->payments->lockForUpdate($payment);

            if (! $payment->isConfirmed()) {
                throw ValidationException::withMessages([
                    'reason' => 'Only a confirmed payment can be voided.',
                ]);
            }

            $this->payments->update($payment, [
                'status' => ShopOrderPayment::STATUS_VOIDED,
                'voided_at' => now(),
                'voided_by' => $actor->id,
                'void_reason' => $reason,
            ]);

            $this->sync($locked);

            return $payment;
        });
    }

    /**
     * Record the payment taken while the order is created. The caller owns the transaction.
     */
    public function recordInitialPayment(User $actor, ShopOrder $order, float $amount, ?string $method): ?ShopOrderPayment
    {
        if ($amount <= 0) {
            return null;
        }

        return $this->addConfirmed($order, [
            'amount' => $amount,
            'method' => $method ?: 'cash',
            'source' => ShopOrderPayment::SOURCE_SHOP,
            'recorded_by' => $actor->id,
        ]);
    }

    /**
     * Confirm an online payment once. Safe to call repeatedly or from several places at once:
     * a payment already confirmed, cancelled or voided is left alone.
     */
    public function confirmOnline(ShopOrderPayment $payment, ?string $paymongoPaymentId = null): bool
    {
        $notify = null;

        $confirmed = DB::transaction(function () use ($payment, $paymongoPaymentId, &$notify) {
            $order = $this->orders->lockForUpdate($payment->order()->firstOrFail());
            $payment = $this->payments->lockForUpdate($payment);

            // A checkout we gave up on can still be paid; the money is real, so it is counted.
            $revivable = $payment->status === ShopOrderPayment::STATUS_CANCELLED
                && $payment->source === ShopOrderPayment::SOURCE_ONLINE;

            if (! $payment->isPending() && ! $revivable) {
                return $payment->isConfirmed();
            }

            $balance = (float) $order->total_amount - $this->payments->confirmedTotal($order->id);
            if ($order->status === 'cancelled' || (float) $payment->amount > $balance + 0.004) {
                // The customer really paid, so the payment is kept; the shop needs to refund the excess.
                Log::warning('Online payment received beyond the order balance', [
                    'order_id' => $order->id,
                    'payment' => $payment->public_id,
                    'order_status' => $order->status,
                    'balance' => round($balance, 2),
                ]);
            }

            $this->payments->update($payment, [
                'status' => ShopOrderPayment::STATUS_CONFIRMED,
                'paid_at' => now(),
                'paymongo_payment_id' => $paymongoPaymentId,
            ]);

            $order = $this->sync($order);

            if ($paymongoPaymentId) {
                $order = $this->orders->update($order, ['paymongo_payment_id' => $paymongoPaymentId]);
            }

            $notify = [$order, (float) $payment->amount];

            return true;
        });

        if ($notify) {
            $this->notifyCustomer($notify[0], $notify[1]);
        }

        return $confirmed;
    }

    /** An online payment that was abandoned or expired. Nothing was charged. */
    public function cancelPending(ShopOrderPayment $payment): void
    {
        DB::transaction(function () use ($payment) {
            $payment = $this->payments->lockForUpdate($payment);

            if ($payment->isPending()) {
                $this->payments->update($payment, ['status' => ShopOrderPayment::STATUS_CANCELLED]);
            }
        });
    }

    /**
     * Recalculate the order's paid total, status and paid date from its confirmed payments.
     * The order row must already be locked by the caller.
     */
    public function sync(ShopOrder $order): ShopOrder
    {
        $paid = $this->payments->confirmedTotal($order->id);
        $total = round((float) $order->total_amount, 2);

        $status = match (true) {
            $paid <= 0 => 'unpaid',
            $paid < $total => 'partial',
            default => 'paid',
        };

        return $this->orders->update($order, [
            'amount_paid' => $paid,
            'payment_status' => $status,
            'paid_at' => $status === 'paid' ? $this->payments->lastConfirmedAt($order->id) : null,
        ]);
    }

    /** Add a confirmed payment to a locked order after checking it against what is still owed. */
    private function addConfirmed(ShopOrder $order, array $attributes): ShopOrderPayment
    {
        if ($order->status === 'cancelled') {
            throw ValidationException::withMessages(['amount' => 'A cancelled order cannot receive payments.']);
        }

        $amount = round((float) $attributes['amount'], 2);
        $balance = round(max(0, (float) $order->total_amount - $this->payments->confirmedTotal($order->id)), 2);

        if ($balance <= 0) {
            throw ValidationException::withMessages(['amount' => 'This order has already been paid in full.']);
        }

        if ($amount <= 0) {
            throw ValidationException::withMessages(['amount' => 'The amount must be greater than zero.']);
        }

        if ($amount > $balance) {
            throw ValidationException::withMessages([
                'amount' => 'The amount is more than the remaining balance of ₱'.number_format($balance, 2).'.',
            ]);
        }

        $payment = $this->payments->create([
            ...$attributes,
            'shop_order_id' => $order->id,
            'shop_id' => $order->shop_id,
            'amount' => $amount,
            'status' => ShopOrderPayment::STATUS_CONFIRMED,
            'paid_at' => now(),
        ]);

        $this->sync($order);
        $this->orders->update($order, ['payment_method' => $attributes['method']]);

        return $payment;
    }

    private function notifyCustomer(ShopOrder $order, float $amount): void
    {
        $order = $order->fresh(['shop', 'customer']);

        if ($order?->user_id) {
            $order->customer?->notify(new OrderNotification($order, 'payment_updated', null, $amount));
        }
    }

    private function assertOrderBelongsToActorShop(User $actor, ShopOrder $order): void
    {
        if ($order->shop_id !== $this->shopContext->shopIdFor($actor)) {
            throw (new ModelNotFoundException)->setModel(ShopOrder::class);
        }
    }
}
