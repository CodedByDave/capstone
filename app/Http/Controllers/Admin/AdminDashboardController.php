<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Shop;
use App\Models\User;
use App\Models\Order;
use App\Models\Payment;
use App\Models\Employee;
use App\Models\Inventory;
use Inertia\Inertia;

class AdminDashboardController extends Controller
{
    public function index()
    {
        $now         = now();
        $thisMonth   = now()->startOfMonth();
        $lastMonth   = now()->subMonth()->startOfMonth();
        $lastMonthEnd = now()->subMonth()->endOfMonth();

        // ── Shops ─────────────────────────────────────────────────────────────
        $totalShops      = Shop::count();
        $shopsThisMonth  = Shop::where('created_at', '>=', $thisMonth)->count();
        $shopsLastMonth  = Shop::whereBetween('created_at', [$lastMonth, $lastMonthEnd])->count();
        $shopChange      = $shopsLastMonth > 0
            ? round((($shopsThisMonth - $shopsLastMonth) / $shopsLastMonth) * 100, 1)
            : 0;
        $activeShops     = Shop::where('status', 'active')->count();
        $disabledShops   = Shop::where('status', 'disabled')->count();
        $pendingShops    = Shop::where('status', 'pending')->count();

        // ── Users ─────────────────────────────────────────────────────────────
        $totalOwners     = User::where('role', User::ROLE_OWNER)->count();
        $totalStaff      = User::whereIn('role', [User::ROLE_STAFF, User::ROLE_MANAGER])->count();
        $totalCustomers  = User::where('role', User::ROLE_USER)->count();
        $newUsersMonth   = User::where('created_at', '>=', $thisMonth)->count();
        $newUsersLast    = User::whereBetween('created_at', [$lastMonth, $lastMonthEnd])->count();
        $usersChange     = $newUsersLast > 0
            ? round((($newUsersMonth - $newUsersLast) / $newUsersLast) * 100, 1)
            : 0;

        // ── Orders / Subscriptions ────────────────────────────────────────────
        $totalOrders        = Order::count();
        $activeSubscriptions = Order::where('status', 'paid')
            ->where('expires_at', '>', $now)
            ->count();
        $expiringThisWeek   = Order::where('status', 'paid')
            ->whereBetween('expires_at', [$now, $now->copy()->addDays(7)])
            ->with('user')
            ->get();
        $expiredOrders      = Order::where('status', 'paid')
            ->where('expires_at', '<', $now)
            ->count();
        $ordersThisMonth    = Order::where('created_at', '>=', $thisMonth)->count();
        $ordersLastMonth    = Order::whereBetween('created_at', [$lastMonth, $lastMonthEnd])->count();
        $ordersChange       = $ordersLastMonth > 0
            ? round((($ordersThisMonth - $ordersLastMonth) / $ordersLastMonth) * 100, 1)
            : 0;

        $planBreakdown = Order::where('status', 'paid')
            ->where('expires_at', '>', now())
            ->selectRaw('billing_months, COUNT(*) as total')
            ->groupBy('billing_months')
            ->pluck('total', 'billing_months');


        // ── Payments / Revenue ────────────────────────────────────────────────
        $revenueThisMonth = Payment::where('status', 'paid')
            ->where('created_at', '>=', $thisMonth)
            ->sum('amount');
        $revenueLastMonth = Payment::where('status', 'paid')
            ->whereBetween('created_at', [$lastMonth, $lastMonthEnd])
            ->sum('amount');
        $revenueChange    = $revenueLastMonth > 0
            ? round((($revenueThisMonth - $revenueLastMonth) / $revenueLastMonth) * 100, 1)
            : 0;
        $totalRevenue     = Payment::where('status', 'paid')->sum('amount');

        // Revenue by month (current year)
        $revenueByMonth = Payment::where('status', 'paid')
            ->whereYear('created_at', $now->year)
            ->selectRaw('MONTH(created_at) as month, SUM(amount) as total')
            ->groupBy('month')
            ->orderBy('month')
            ->pluck('total', 'month');

        // Orders by month (current year)
        $ordersByMonth = Order::whereYear('created_at', $now->year)
            ->selectRaw('MONTH(created_at) as month, COUNT(*) as total')
            ->groupBy('month')
            ->orderBy('month')
            ->pluck('total', 'month');

        // Shop registrations by month (current year)
        $shopsByMonth = Shop::whereYear('created_at', $now->year)
            ->selectRaw('MONTH(created_at) as month, COUNT(*) as total')
            ->groupBy('month')
            ->orderBy('month')
            ->pluck('total', 'month');

        $months = collect(range(1, 12));
        $revenueChart = $months->map(fn($m) => (float)($revenueByMonth[$m] ?? 0))->values();
        $ordersChart  = $months->map(fn($m) => (int)($ordersByMonth[$m]   ?? 0))->values();
        $shopsChart   = $months->map(fn($m) => (int)($shopsByMonth[$m]    ?? 0))->values();

        // ── Employees ─────────────────────────────────────────────────────────
        $totalEmployees  = Employee::count();
        $activeEmployees = Employee::where('status', 'Active')->count();

        // ── Inventory ─────────────────────────────────────────────────────────
        $totalInventory   = Inventory::count();
        $lowStockCount    = Inventory::whereColumn('quantity', '<=', 'min_stock')->count();
        $outOfStockCount  = Inventory::where('quantity', 0)->count();

        // ── Alerts ────────────────────────────────────────────────────────────
        $overduePayments = Order::where('status', 'paid')
            ->where('expires_at', '<', $now->copy()->subDays(7))
            ->with('user')
            ->get()
            ->map(fn($o) => $o->shop_name ?? $o->owner_name);

        $inactiveShops = Shop::where('status', 'active')
            ->where('updated_at', '<', $now->copy()->subDays(21))
            ->pluck('shop_name');

        // ── Tenant table ──────────────────────────────────────────────────────────────
        $shops = Shop::with(['owner'])
            ->latest()
            ->take(10)
            ->get()
            ->map(function ($shop) use ($now) {
                // Get latest paid order for this shop's owner
                $latestOrder = Order::where('user_id', $shop->owner_id)
                    ->where('status', 'paid')
                    ->latest()
                    ->first();

                return [
                    'name'        => $shop->shop_name,
                    'owner'       => $shop->owner?->name ?? '—',
                    'plan'        => $latestOrder?->subscription_plan ?? 'None',
                    'status'      => $shop->status,
                    'expiry'      => $latestOrder?->expires_at
                        ? \Carbon\Carbon::parse($latestOrder->expires_at)->format('Y-m-d')
                        : '—',
                    'revenue'     => $latestOrder?->total_price
                        ? '₱' . number_format($latestOrder->total_price, 2)
                        : '₱0.00',
                    'is_expiring' => $latestOrder?->expires_at
                        && \Carbon\Carbon::parse($latestOrder->expires_at)->between($now, $now->copy()->addDays(7)),
                ];
            });

        return Inertia::render('admin/Dashboard', [
            'kpis' => [
                // Shops
                'totalShops'    => $totalShops,
                'activeShops'   => $activeShops,
                'disabledShops' => $disabledShops,
                'pendingShops'  => $pendingShops,
                'shopChange'    => $shopChange,
                // Users
                'totalOwners'   => $totalOwners,
                'totalStaff'    => $totalStaff,
                'totalCustomers' => $totalCustomers,
                'newUsersMonth' => $newUsersMonth,
                'usersChange'   => $usersChange,
                // Orders
                'totalOrders'        => $totalOrders,
                'activeSubscriptions' => $activeSubscriptions,
                'expiredOrders'      => $expiredOrders,
                'ordersThisMonth'    => $ordersThisMonth,
                'ordersChange'       => $ordersChange,
                // Revenue
                'revenueThisMonth' => (float) $revenueThisMonth,
                'revenueLastMonth' => (float) $revenueLastMonth,
                'revenueChange'    => $revenueChange,
                'totalRevenue'     => (float) $totalRevenue,
                // Employees & Inventory
                'totalEmployees'  => $totalEmployees,
                'activeEmployees' => $activeEmployees,
                'totalInventory'  => $totalInventory,
                'lowStockCount'   => $lowStockCount,
                'outOfStockCount' => $outOfStockCount,
            ],
            'alerts' => [
                'overduePayments' => $overduePayments->values(),
                'expiringShops'   => $expiringThisWeek->map(fn($o) => $o->shop_name)->values(),
                'inactiveShops'   => $inactiveShops->values(),
                'pendingShops'    => $pendingShops,
                'lowStockCount'   => $lowStockCount,
                'outOfStockCount' => $outOfStockCount,
            ],
            'charts' => [
                'revenue' => $revenueChart,
                'orders'  => $ordersChart,
                'shops'   => $shopsChart,
            ],
            'planBreakdown' => $planBreakdown,
            'shops'         => $shops,
        ]);
    }
}
