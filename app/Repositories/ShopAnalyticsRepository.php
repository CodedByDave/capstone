<?php

namespace App\Repositories;

use App\Models\Attendance;
use App\Models\EmployeeSchedule;
use App\Models\Expense;
use App\Models\Inventory;
use App\Models\InventoryMovement;
use App\Models\PayrollItem;
use App\Models\ShopOrder;
use App\Models\ShopService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

/** Read-only aggregates behind the Decision Support insights and the finance/operations reports. */
class ShopAnalyticsRepository extends Repository
{
    public function __construct()
    {
        parent::__construct(new ShopOrder);
    }

    // ── Finance ──────────────────────────────────────────────────────────────

    public function revenueBetween(int $shopId, Carbon $start, Carbon $end): float
    {
        return (float) $this->orders($shopId)
            ->whereBetween('paid_at', [$start, $end])
            ->sum('amount_paid');
    }

    public function expensesBetween(int $shopId, Carbon $start, Carbon $end): float
    {
        return (float) Expense::query()
            ->where('shop_id', $shopId)
            ->whereBetween('expense_date', [$start->toDateString(), $end->toDateString()])
            ->sum('amount');
    }

    /** @return Collection<int, array{name: string, total: float}> Largest expense categories first. */
    public function expensesByCategory(int $shopId, Carbon $start, Carbon $end): Collection
    {
        return Expense::query()
            ->where('shop_id', $shopId)
            ->whereBetween('expense_date', [$start->toDateString(), $end->toDateString()])
            ->with('category:id,name')
            ->selectRaw('category_id, COALESCE(SUM(amount), 0) as total')
            ->groupBy('category_id')
            ->orderByDesc('total')
            ->get()
            ->map(fn ($row) => [
                'name' => $row->category?->name ?? 'Uncategorized',
                'total' => (float) $row->total,
            ]);
    }

    /** Revenue per calendar month, keyed by Y-m. */
    public function monthlyRevenue(int $shopId, Carbon $since): Collection
    {
        return $this->orders($shopId)
            ->where('paid_at', '>=', $since)
            ->selectRaw("DATE_FORMAT(paid_at, '%Y-%m') as month, COALESCE(SUM(amount_paid), 0) as total")
            ->groupBy('month')
            ->pluck('total', 'month')
            ->map(fn ($v) => (float) $v);
    }

    /** Expenses per calendar month, keyed by Y-m. */
    public function monthlyExpenses(int $shopId, Carbon $since): Collection
    {
        return Expense::query()
            ->where('shop_id', $shopId)
            ->where('expense_date', '>=', $since->toDateString())
            ->selectRaw("DATE_FORMAT(expense_date, '%Y-%m') as month, COALESCE(SUM(amount), 0) as total")
            ->groupBy('month')
            ->pluck('total', 'month')
            ->map(fn ($v) => (float) $v);
    }

    // ── Operations ───────────────────────────────────────────────────────────

    public function ordersCreatedBetween(int $shopId, Carbon $start, Carbon $end): int
    {
        return $this->orders($shopId)->whereBetween('created_at', [$start, $end])->count();
    }

    /** @return Collection<string, int> Order counts keyed by status for orders created in the range. */
    public function orderStatusCounts(int $shopId, Carbon $start, Carbon $end): Collection
    {
        return $this->orders($shopId)
            ->whereBetween('created_at', [$start, $end])
            ->selectRaw('status, COUNT(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status')
            ->map(fn ($v) => (int) $v);
    }

    /** Average hours between an order being placed and completed, or null without finished orders. */
    public function averageTurnaroundHours(int $shopId, Carbon $start, Carbon $end): ?float
    {
        $minutes = $this->orders($shopId)
            ->whereBetween('created_at', [$start, $end])
            ->whereNotNull('completed_at')
            ->selectRaw('AVG(TIMESTAMPDIFF(MINUTE, created_at, completed_at)) as minutes')
            ->value('minutes');

        return $minutes === null ? null : round(((float) $minutes) / 60, 1);
    }

    /** @return Collection<int, array{name: string, orders: int, revenue: float}> Services ranked by revenue. */
    public function servicePerformance(int $shopId, Carbon $start, Carbon $end, int $limit = 8): Collection
    {
        return $this->orders($shopId)
            ->whereBetween('created_at', [$start, $end])
            ->where('status', '!=', 'cancelled')
            ->with('service:id,service_name')
            ->selectRaw('service_id, COUNT(*) as orders, COALESCE(SUM(total_amount), 0) as revenue')
            ->groupBy('service_id')
            ->orderByDesc('revenue')
            ->limit($limit)
            ->get()
            ->map(fn ($row) => [
                'name' => $row->service?->service_name ?? 'Unknown',
                'orders' => (int) $row->orders,
                'revenue' => (float) $row->revenue,
            ]);
    }

    /** @return Collection<int, int> Non-cancelled orders per service created in the range. */
    public function orderCountsByService(int $shopId, Carbon $start, Carbon $end): Collection
    {
        return $this->orders($shopId)
            ->whereBetween('created_at', [$start, $end])
            ->where('status', '!=', 'cancelled')
            ->selectRaw('service_id, COUNT(*) as total')
            ->groupBy('service_id')
            ->pluck('total', 'service_id')
            ->map(fn ($v) => (int) $v);
    }

    /** @return Collection<int, ShopService> */
    public function activeServicesWithSupplies(int $shopId): Collection
    {
        return ShopService::query()
            ->where('shop_id', $shopId)
            ->where('is_active', true)
            ->with('supplies:id,unit_price')
            ->get();
    }

    /** Order counts per weekday (1 = Sunday) for orders created in the range. */
    public function ordersByWeekday(int $shopId, Carbon $start, Carbon $end): Collection
    {
        return $this->orders($shopId)
            ->whereBetween('created_at', [$start, $end])
            ->selectRaw('DAYOFWEEK(created_at) as dow, COUNT(*) as total')
            ->groupBy('dow')
            ->pluck('total', 'dow')
            ->map(fn ($v) => (int) $v);
    }

    /** @return Collection<string, int> Orders placed per calendar day, keyed by Y-m-d. */
    public function dailyOrderCounts(int $shopId, Carbon $start, Carbon $end): Collection
    {
        return $this->orders($shopId)
            ->whereBetween('created_at', [$start, $end])
            ->selectRaw('DATE(created_at) as day, COUNT(*) as total')
            ->groupBy('day')
            ->pluck('total', 'day')
            ->map(fn ($v) => (int) $v);
    }

    // ── Inventory ────────────────────────────────────────────────────────────

    public function lowStockCount(int $shopId): int
    {
        return Inventory::forShop($shopId)->lowStock()->where('quantity', '>', 0)->count();
    }

    public function outOfStockCount(int $shopId): int
    {
        return Inventory::forShop($shopId)->outOfStock()->count();
    }

    /**
     * Active items with recorded consumption since `$since`, with their supplier's lead time.
     *
     * @return Collection<int, array{id: int, name: string, unit: ?string, quantity: int, used: int, lead_time_days: ?int}>
     */
    public function consumptionSince(int $shopId, Carbon $since): Collection
    {
        $used = InventoryMovement::query()
            ->where('shop_id', $shopId)
            ->whereIn('type', ['usage', 'damage'])
            ->where('created_at', '>=', $since)
            ->selectRaw('inventory_id, SUM(ABS(quantity)) as used')
            ->groupBy('inventory_id')
            ->pluck('used', 'inventory_id');

        if ($used->isEmpty()) {
            return collect();
        }

        return Inventory::forShop($shopId)
            ->where('status', 'active')
            ->whereIn('id', $used->keys())
            ->with('supplier:id,lead_time_days')
            ->get(['id', 'name', 'unit', 'quantity', 'supplier_id'])
            ->map(fn (Inventory $item) => [
                'id' => $item->id,
                'name' => $item->name,
                'unit' => $item->unit,
                'quantity' => (int) $item->quantity,
                'used' => (int) $used[$item->id],
                'lead_time_days' => $item->supplier?->lead_time_days,
            ])
            ->values();
    }

    // ── HRM ──────────────────────────────────────────────────────────────────

    /** @return Collection<string, int> Active employees on the weekly schedule, keyed by day name (e.g. "Monday"). */
    public function scheduledStaffByDay(int $shopId): Collection
    {
        return EmployeeSchedule::query()
            ->whereHas('employee', fn (Builder $q) => $q->where('shop_id', $shopId)->where('status', 'Active'))
            ->selectRaw('day, COUNT(DISTINCT employee_id) as total')
            ->groupBy('day')
            ->pluck('total', 'day')
            ->map(fn ($v) => (int) $v);
    }

    /** @return Collection<string, int> Attendance record counts keyed by status. */
    public function attendanceCounts(int $shopId, Carbon $since): Collection
    {
        return Attendance::query()
            ->where('shop_id', $shopId)
            ->where('date', '>=', $since->toDateString())
            ->selectRaw('status, COUNT(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status')
            ->map(fn ($v) => (int) $v);
    }

    /** Net pay of payrolls whose period ended in the range. */
    public function payrollCostBetween(int $shopId, Carbon $start, Carbon $end): float
    {
        return (float) PayrollItem::query()
            ->whereHas('payroll', fn (Builder $q) => $q
                ->where('shop_id', $shopId)
                ->whereBetween('period_end', [$start->toDateString(), $end->toDateString()]))
            ->sum('net_pay');
    }

    private function orders(int $shopId): Builder
    {
        return ShopOrder::query()->forShop($shopId);
    }
}
