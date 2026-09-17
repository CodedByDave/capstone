<?php

namespace App\Repositories;

use App\Models\Shop;
use App\Models\User;
use App\Models\Order;
use App\Models\Payment;
use App\Models\Employee;
use App\Models\Inventory;
use Carbon\CarbonPeriod;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

class AdminDashboardRepository extends Repository
{
    public function __construct()
    {
        parent::__construct(new Shop());
    }

    // Shop related informations methods
    public function shopsTotalCount(): int
    {
        return Shop::count();
    }

    public function shopsCountSince(Carbon $date): int
    {
        return Shop::where('created_at', '>=', $date)->count();
    }

    public function shopsCountBetween(Carbon $start, Carbon $end): int
    {
        return Shop::whereBetween('created_at', [$start, $end])->count();
    }

    public function shopsCountByStatus(string $status): int
    {
        return Shop::where('status', $status)->count();
    }

    public function getRecentShops(Carbon $now): Collection
    {
        return Shop::with('owner')
            ->latest()
            ->take(10)
            ->get()
            ->map(function (Shop $shop) use ($now) {
                $latestOrder = Order::where('user_id', $shop->owner_id)
                    ->whereIn('status', ['paid', 'approved'])
                    ->latest()
                    ->first();

                return [
                    'name' => $shop->shop_name,
                    'owner' => $shop->owner?->name ?? '—',
                    'plan' => $latestOrder?->plan_name ?? 'None',
                    'status' => $shop->status,
                    'expiry' => $latestOrder?->expires_at
                        ? Carbon::parse($latestOrder->expires_at)->format('Y-m-d')
                        : '—',
                    'revenue' => $latestOrder?->total_price
                        ? '₱' . number_format($latestOrder->total_price, 2)
                        : '₱0.00',
                    'is_expiring' => $latestOrder?->expires_at
                        && Carbon::parse($latestOrder->expires_at)->between($now, $now->copy()->addDays(7)),
                ];
            });
    }


    // User related informations methods
    public function usersCountByRole(string $role): int
    {
        return User::where('role', $role)->count();
    }

    public function usersCountSince(Carbon $date): int
    {
        return User::where('created_at', '>=', $date)->count();
    }

    public function usersCountBetween(Carbon $start, Carbon $end): int
    {
        return User::whereBetween('created_at', [$start, $end])->count();
    }

    // Orders/Subcriptions related informations methods
    public function ordersTotalCount(): int
    {
        return Order::count();
    }

    public function activeSubcriptionsCount(Carbon $now): int
    {
        return Order::whereIn('status', ['paid', 'approved'])
            ->where('expires_at', '>', $now)
            ->count();
    }

    public function expiringSubscriptionsInSevenDays(Carbon $now): Collection
    {
        $targetDate = $now->copy()->addDays(7);
        return Order::whereIn('status', ['paid', 'approved'])
            ->whereBetween('expires_at', [
                $targetDate->copy()->startOfDay(),
                $targetDate->copy()->endOfDay()
            ])
            ->with('user')
            ->get();
    }

    public function expiredSubscriptionsCount(Carbon $now): int
    {
        return Order::whereIn('status', ['paid', 'approved'])
            ->where('expires_at', '<', $now)
            ->count();
    }

    public function ordersCountSince(Carbon $date): int
    {
        return Order::where('created_at', '>=', $date)->count();
    }

    public function ordersCountBetween(Carbon $start, Carbon $end): int
    {
        return Order::whereBetween('created_at', [$start, $end])->count();
    }

    // Active subscription breakdown by plan name.
    public function getPlanBreakdown(Carbon $now): Collection
    {
        return Order::whereIn('status', ['paid', 'approved'])
            ->where('expires_at', '>', $now)
            ->selectRaw('plan_name, COUNT(*) as total')
            ->groupBy('plan_name')
            ->orderBy('plan_name')
            ->pluck('total', 'plan_name');
    }

    // Revenue by month (current year)
    public function getMonthlyRevenue(Carbon $now): Collection
    {
        $paymentRevenue = Payment::where('status', 'paid')
            ->where(function ($query) use ($now) {
                $query->whereYear('paid_at', $now->year)
                    ->orWhere(function ($legacyPayment) use ($now) {
                        $legacyPayment->whereNull('paid_at')
                            ->whereYear('created_at', $now->year);
                    });
            })
            ->selectRaw('MONTH(COALESCE(paid_at, created_at)) as month, SUM(amount) as total')
            ->groupBy('month')
            ->orderBy('month')
            ->pluck('total', 'month');

        $orderRevenue = $this->ordersWithoutPaidPayment()
            ->whereYear('created_at', $now->year)
            ->selectRaw('MONTH(created_at) as month, SUM(total_price) as total')
            ->groupBy('month')
            ->orderBy('month')
            ->pluck('total', 'month');

        return $paymentRevenue->keys()
            ->merge($orderRevenue->keys())
            ->unique()
            ->mapWithKeys(fn ($month) => [
                $month => (float) ($paymentRevenue[$month] ?? 0)
                    + (float) ($orderRevenue[$month] ?? 0),
            ]);
    }

    // Orders by month (current year)
    public function getMonthlyOrders(Carbon $now): Collection
    {
        return Order::query()
            ->whereYear('created_at', $now->year)
            ->selectRaw('MONTH(created_at) as month, COUNT(*) as total')
            ->groupBy('month')
            ->orderBy('month')
            ->pluck('total', 'month');
    }

    public function revenueBetween(Carbon $start, Carbon $end): float
    {
        $paymentRevenue = (float) Payment::where('status', 'paid')
            ->where(function ($query) use ($start, $end) {
                $query->whereBetween('paid_at', [$start, $end])
                    ->orWhere(function ($legacyPayment) use ($start, $end) {
                        $legacyPayment->whereNull('paid_at')
                            ->whereBetween('created_at', [$start, $end]);
                    });
            })
            ->sum('amount');

        $orderRevenue = (float) $this->ordersWithoutPaidPayment()
            ->whereBetween('created_at', [$start, $end])
            ->sum('total_price');

        return $paymentRevenue + $orderRevenue;
    }

    public function totalRevenue(): float
    {
        $paymentRevenue = (float) Payment::where('status', 'paid')->sum('amount');
        $orderRevenue = (float) $this->ordersWithoutPaidPayment()->sum('total_price');

        return $paymentRevenue + $orderRevenue;
    }

    private function ordersWithoutPaidPayment(): Builder
    {
        return Order::query()
            ->whereIn('status', ['approved', 'paid', 'expired'])
            ->where('is_trial', false)
            ->where('total_price', '>', 0)
            ->whereDoesntHave('payments', fn ($query) =>
                $query->where('status', 'paid'));
    }

    public function getMonthlyShopRegistrations(Carbon $now): Collection
    {
        return Shop::whereYear('created_at', $now->year)
            ->selectRaw('MONTH(created_at) as month, COUNT(*) as total')
            ->groupBy('month')
            ->orderBy('month')
            ->pluck('total', 'month');
    }

    public function employeesTotalCount(): int
    {
        return Employee::count();
    }

    public function employeesCountByStatus(string $status): int
    {
        return Employee::where('status', $status)->count();
    }

    public function inventoryTotalCount(): int
    {
        return Inventory::count();
    }

    public function inventoryLowStockCount(): int
    {
        return Inventory::whereColumn('quantity', '<=', 'min_stock')->count();
    }

    public function inventoryOutOfStockCount(): int
    {
        return Inventory::where('quantity', 0)->count();
    }

    // Alerts for overdue subscriptions (expired) - for monitoring which shops have not renewed their subscriptions for more than 7 days
    public function getOverdueSubscriptions(Carbon $now): Collection
    {
        return Order::whereIn('status', ['paid', 'approved'])
            ->where('expires_at', '<', $now->copy()->subDays(7))
            ->with('user')
            ->get();
    }

    // Monitoring shop activity - for monitoring which shops have not been active for more than 30 days
    public function getInactiveShops(Carbon $now): Collection
    {
        return Shop::where('status', 'active')
            ->where('last_activity_at', '<', $now->copy()->subDays(30))
            ->with('owner')
            ->get();
    }
}
