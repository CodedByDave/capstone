<?php

namespace App\Repositories;

use App\Models\ShopOrder;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

class ShopDashboardRepository extends Repository
{
    private const OPEN_STATUSES = ['pending', 'in_progress'];

    public function __construct()
    {
        parent::__construct(new ShopOrder);
    }

    /** Money received on orders between two moments. */
    public function revenueBetween(int $shopId, Carbon $start, Carbon $end): float
    {
        return (float) $this->orders($shopId)
            ->whereBetween('paid_at', [$start, $end])
            ->sum('amount_paid');
    }

    /** Revenue per calendar day, keyed by Y-m-d, from `$start` to now. */
    public function dailyRevenue(int $shopId, Carbon $start): Collection
    {
        return $this->orders($shopId)
            ->where('paid_at', '>=', $start)
            ->selectRaw('DATE(paid_at) as day, COALESCE(SUM(amount_paid), 0) as revenue')
            ->groupBy('day')
            ->pluck('revenue', 'day')
            ->map(fn ($v) => (float) $v);
    }

    public function ordersCreatedBetween(int $shopId, Carbon $start, Carbon $end): int
    {
        return $this->orders($shopId)->whereBetween('created_at', [$start, $end])->count();
    }

    public function totalOrdersCount(int $shopId): int
    {
        return $this->orders($shopId)->count();
    }

    public function openOrdersCount(int $shopId): int
    {
        return $this->openOrders($shopId)->count();
    }

    /** Open orders already past their estimated completion time. */
    public function overdueOrdersCount(int $shopId, Carbon $now): int
    {
        return $this->openOrders($shopId)->where('estimated_completion_at', '<', $now)->count();
    }

    /** @return array{amount: float, orders: int} Balance still owed on active (not cancelled), not fully paid orders. */
    public function unpaidBalance(int $shopId): array
    {
        $query = $this->orders($shopId)
            ->where('status', '!=', 'cancelled')
            ->where('payment_status', '!=', 'paid')
            ->whereColumn('amount_paid', '<', 'total_amount');

        return [
            'amount' => (float) (clone $query)->selectRaw('COALESCE(SUM(total_amount - amount_paid), 0) as due')->value('due'),
            'orders' => (clone $query)->count(),
        ];
    }

    /** Open orders due before the end of today, soonest first (overdue ones included). */
    public function dueSoon(int $shopId, Carbon $endOfDay, int $limit = 5): Collection
    {
        return $this->openOrders($shopId)
            ->with('service:id,service_name')
            ->whereNotNull('estimated_completion_at')
            ->where('estimated_completion_at', '<=', $endOfDay)
            ->orderBy('estimated_completion_at')
            ->limit($limit)
            ->get();
    }

    /** Order counts per weekday (1 = Sunday), used to find the busiest day. */
    public function ordersByWeekday(int $shopId, Carbon $since): Collection
    {
        return $this->orders($shopId)
            ->where('created_at', '>=', $since)
            ->selectRaw('DAYOFWEEK(created_at) as dow, COUNT(*) as total')
            ->groupBy('dow')
            ->pluck('total', 'dow')
            ->map(fn ($v) => (int) $v);
    }

    private function orders(int $shopId): Builder
    {
        return ShopOrder::query()->forShop($shopId);
    }

    private function openOrders(int $shopId): Builder
    {
        return $this->orders($shopId)->whereIn('status', self::OPEN_STATUSES);
    }
}
