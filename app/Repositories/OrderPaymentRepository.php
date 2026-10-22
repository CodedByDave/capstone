<?php

namespace App\Repositories;

use App\Models\ShopOrderPayment;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Carbon;

/** All reads and writes of the payments made toward shop orders. */
class OrderPaymentRepository
{
    /** How long a cancelled online checkout is still checked in case it was paid anyway. */
    public const CANCELLED_RECHECK_HOURS = 6;

    public function __construct(private readonly ShopOrderPayment $model) {}

    public function create(array $data): ShopOrderPayment
    {
        return $this->model->create($data);
    }

    public function update(ShopOrderPayment $payment, array $data): ShopOrderPayment
    {
        $payment->update($data);

        return $payment;
    }

    /** Re-read a payment with a row lock so two requests cannot confirm or void it twice. */
    public function lockForUpdate(ShopOrderPayment $payment): ShopOrderPayment
    {
        return $this->model->newQuery()->whereKey($payment->getKey())->lockForUpdate()->firstOrFail();
    }

    /** A payment of the given order, looked up by its public id; null when it belongs elsewhere. */
    public function findForOrder(int $orderId, string $publicId): ?ShopOrderPayment
    {
        return $this->model->newQuery()
            ->where('shop_order_id', $orderId)
            ->where('public_id', $publicId)
            ->first();
    }

    /** Payments of one order for display, oldest first. */
    public function historyForOrder(int $orderId): Collection
    {
        return $this->model->newQuery()
            ->with(['recorder:id,name', 'voider:id,name'])
            ->where('shop_order_id', $orderId)
            ->whereIn('status', [
                ShopOrderPayment::STATUS_CONFIRMED,
                ShopOrderPayment::STATUS_VOIDED,
                ShopOrderPayment::STATUS_PENDING,
            ])
            ->orderBy('created_at')
            ->orderBy('id')
            ->get();
    }

    public function confirmedForOrder(int $orderId): Collection
    {
        return $this->model->newQuery()
            ->where('shop_order_id', $orderId)
            ->confirmed()
            ->orderBy('paid_at')
            ->get();
    }

    public function confirmedTotal(int $orderId): float
    {
        return round((float) $this->model->newQuery()
            ->where('shop_order_id', $orderId)
            ->confirmed()
            ->sum('amount'), 2);
    }

    public function lastConfirmedAt(int $orderId): ?Carbon
    {
        $value = $this->model->newQuery()
            ->where('shop_order_id', $orderId)
            ->confirmed()
            ->max('paid_at');

        return $value ? Carbon::parse($value) : null;
    }

    // ─── Income reporting ─────────────────────────────────────────────────────
    // Income is the confirmed payments by the day they were received. Payments on archived
    // orders are left out, as the order-based figures always did.

    private function incomeQuery(int $shopId, ?string $from = null, ?string $to = null, ?string $method = null)
    {
        return $this->model->newQuery()
            ->where('shop_id', $shopId)
            ->confirmed()
            ->whereHas('order')
            ->when($from, fn ($q) => $q->where('paid_at', '>=', $from.' 00:00:00'))
            ->when($to, fn ($q) => $q->where('paid_at', '<=', $to.' 23:59:59'))
            ->when($method, fn ($q) => $q->where('method', $method));
    }

    public function paginateIncome(int $shopId, ?string $from, ?string $to, ?string $method, int $perPage = 15): LengthAwarePaginator
    {
        return $this->incomeQuery($shopId, $from, $to, $method)
            ->with('order:id,order_number,customer_name,service_id', 'order.service:id,service_name')
            ->latest('paid_at')
            ->latest('id')
            ->paginate($perPage)
            ->withQueryString();
    }

    public function incomeBetween(int $shopId, string $from, string $to): Collection
    {
        return $this->incomeQuery($shopId, $from, $to)
            ->with('order:id,order_number,customer_name,service_id', 'order.service:id,service_name')
            ->get();
    }

    public function incomeTotal(int $shopId, ?string $from = null, ?string $to = null): float
    {
        return round((float) $this->incomeQuery($shopId, $from, $to)->sum('amount'), 2);
    }

    /** How many orders have received money, counting each order once. */
    public function paidOrderCount(int $shopId): int
    {
        return $this->incomeQuery($shopId)->distinct()->count('shop_order_id');
    }

    public function pendingForOrder(int $orderId): Collection
    {
        return $this->model->newQuery()
            ->where('shop_order_id', $orderId)
            ->where('status', ShopOrderPayment::STATUS_PENDING)
            ->latest('id')
            ->get();
    }

    /**
     * Online checkouts of an order that may still turn out to be paid: those waiting, and those
     * given up on recently (a customer can finish an older checkout after starting a newer one).
     */
    public function settleCandidatesForOrder(int $orderId): Collection
    {
        return $this->model->newQuery()
            ->where('shop_order_id', $orderId)
            ->where('source', ShopOrderPayment::SOURCE_ONLINE)
            ->whereNotNull('paymongo_session_id')
            ->where(function ($q) {
                $q->where('status', ShopOrderPayment::STATUS_PENDING)
                    ->orWhere(fn ($q) => $q->where('status', ShopOrderPayment::STATUS_CANCELLED)
                        ->where('created_at', '>=', now()->subHours(self::CANCELLED_RECHECK_HOURS)));
            })
            ->latest('id')
            ->get();
    }

    public function latestPendingForOrder(int $orderId): ?ShopOrderPayment
    {
        return $this->pendingForOrder($orderId)->first();
    }

    /** Pending online payments old enough to have been tried, for the scheduled reconciliation. */
    public function pendingOnlineBetween(Carbon $olderThan, Carbon $newerThan): Collection
    {
        return $this->model->newQuery()
            ->with('order.shop')
            ->where('source', ShopOrderPayment::SOURCE_ONLINE)
            ->whereNotNull('paymongo_session_id')
            ->where(function ($q) {
                $q->where('status', ShopOrderPayment::STATUS_PENDING)
                    ->orWhere(fn ($q) => $q->where('status', ShopOrderPayment::STATUS_CANCELLED)
                        ->where('created_at', '>=', now()->subHours(self::CANCELLED_RECHECK_HOURS)));
            })
            ->where('created_at', '<=', $olderThan)
            ->where('created_at', '>=', $newerThan)
            ->orderBy('id')
            ->get();
    }

    /** Pending online payments that never completed within the allowed window. */
    public function expirePendingBefore(Carbon $cutoff): int
    {
        return $this->model->newQuery()
            ->where('status', ShopOrderPayment::STATUS_PENDING)
            ->where('source', ShopOrderPayment::SOURCE_ONLINE)
            ->where('created_at', '<', $cutoff)
            ->update(['status' => ShopOrderPayment::STATUS_CANCELLED, 'updated_at' => now()]);
    }
}
