<?php

namespace App\Http\Controllers\Admin;

use App\Enums\AccountType;
use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Shop;
use App\Models\User;
use App\Repositories\AdminDashboardRepository;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class AnalyticsController extends Controller
{
    public function __construct(
        private readonly AdminDashboardRepository $dashboardRepository,
    ) {}

    public function index()
    {
        $now = Carbon::now();
        $thisMonth = $now->copy()->startOfMonth();
        $lastMonth = $now->copy()->subMonth()->startOfMonth();
        $lastMonthEnd = $now->copy()->subMonth()->endOfMonth();

        // Stat Cards

        // Total Shops
        $totalShops = Shop::count();
        $shopsThisMonth = Shop::where('created_at', '>=', $thisMonth)->count();
        $shopsLastMonth = Shop::whereBetween('created_at', [$lastMonth, $lastMonthEnd])->count();
        $shopsChange = $shopsLastMonth > 0
            ? round((($shopsThisMonth - $shopsLastMonth) / $shopsLastMonth) * 100, 1)
            : ($shopsThisMonth > 0 ? 100 : 0);

        // Total Customers (role = 'user')
        $totalCustomers = User::where('role', AccountType::Customer->value)->count();
        $customersThisMonth = User::where('role', AccountType::Customer->value)->where('created_at', '>=', $thisMonth)->count();
        $customersLastMonth = User::where('role', AccountType::Customer->value)->whereBetween('created_at', [$lastMonth, $lastMonthEnd])->count();
        $customersChange = $customersLastMonth > 0
            ? round((($customersThisMonth - $customersLastMonth) / $customersLastMonth) * 100, 1)
            : ($customersThisMonth > 0 ? 100 : 0);

        // Total Revenue (paid payments)
        $totalRevenue = $this->dashboardRepository->totalRevenue();
        $revenueThisMonth = $this->dashboardRepository->revenueBetween($thisMonth, $now);
        $revenueLastMonth = $this->dashboardRepository->revenueBetween($lastMonth, $lastMonthEnd);
        $revenueChange = $revenueLastMonth > 0
            ? round((($revenueThisMonth - $revenueLastMonth) / $revenueLastMonth) * 100, 1)
            : ($revenueThisMonth > 0 ? 100 : 0);

        // Total Orders (paid orders)
        $totalOrders = Order::where('status', 'paid')->count();
        $ordersThisMonth = Order::where('status', 'paid')->where('created_at', '>=', $thisMonth)->count();
        $ordersLastMonth = Order::where('status', 'paid')->whereBetween('created_at', [$lastMonth, $lastMonthEnd])->count();
        $ordersChange = $ordersLastMonth > 0
            ? round((($ordersThisMonth - $ordersLastMonth) / $ordersLastMonth) * 100, 1)
            : ($ordersThisMonth > 0 ? 100 : 0);

        $months = collect(range(0, 11))->map(
            fn (int $offset) => $now->copy()->subMonths(11 - $offset)->startOfMonth(),
        );
        $reportStart = $months->first()->copy()->startOfMonth();
        $reportEnd = $months->last()->copy()->endOfMonth();

        // Revenue Chart last 12 months
        $revenueByMonth = $this->dashboardRepository
            ->revenueByMonthBetween($reportStart, $reportEnd);
        $revenueChart = $months->map(fn (Carbon $month) => [
            'month' => $month->format('M'),
            'amount' => (float) ($revenueByMonth[$month->format('Y-m')] ?? 0),
        ]);

        // Orders Chart last 12 months

        $ordersByMonth = Order::where('status', 'paid')
            ->whereBetween('created_at', [$reportStart, $reportEnd])
            ->select(
                DB::raw("DATE_FORMAT(created_at, '%Y-%m') as month_key"),
                DB::raw('COUNT(*) as count')
            )
            ->groupBy('month_key')
            ->pluck('count', 'month_key');
        $ordersChart = $months->map(fn (Carbon $month) => [
            'month' => $month->format('M'),
            'count' => (int) ($ordersByMonth[$month->format('Y-m')] ?? 0),
        ]);

        // Registration Chart last 12 months
        $registrationsByMonth = User::where('role', AccountType::Customer->value)
            ->whereBetween('created_at', [$reportStart, $reportEnd])
            ->select(
                DB::raw("DATE_FORMAT(created_at, '%Y-%m') as month_key"),
                DB::raw('COUNT(*) as count')
            )
            ->groupBy('month_key')
            ->pluck('count', 'month_key');
        $registrationsChart = $months->map(fn (Carbon $month) => [
            'month' => $month->format('M'),
            'count' => (int) ($registrationsByMonth[$month->format('Y-m')] ?? 0),
        ]);

        // Top Performer shop
        $topShops = Shop::select(
            'shops.id',
            'shops.owner_id',
            'shops.shop_name',
            'shops.municipality',
            'shops.barangay',
            DB::raw('COALESCE(SUM(p.amount), 0) as revenue'),
            DB::raw('COUNT(DISTINCT o.id) as orders')
        )
            ->selectRaw(
                'COALESCE(SUM(CASE WHEN p.paid_at >= ? THEN p.amount ELSE 0 END), 0) as revenue_this_month',
                [$thisMonth],
            )
            ->selectRaw(
                'COALESCE(SUM(CASE WHEN p.paid_at BETWEEN ? AND ? THEN p.amount ELSE 0 END), 0) as revenue_last_month',
                [$lastMonth, $lastMonthEnd],
            )
            ->leftJoin('orders as o', function ($join) {
                $join->on('o.user_id', '=', 'shops.owner_id')
                    ->where('o.status', '=', 'paid');
            })
            ->leftJoin('payments as p', function ($join) {
                $join->on('p.order_id', '=', 'o.id')
                    ->where('p.status', '=', 'paid');
            })
            ->groupBy('shops.id', 'shops.owner_id', 'shops.shop_name', 'shops.municipality', 'shops.barangay')
            ->orderByDesc('revenue')
            ->limit(5)
            ->get()
            ->map(function ($shop, $index) {
                $revenueThisMonth = (float) $shop->revenue_this_month;
                $revenueLastMonth = (float) $shop->revenue_last_month;
                $growth = $revenueLastMonth > 0
                    ? round((($revenueThisMonth - $revenueLastMonth) / $revenueLastMonth) * 100, 1)
                    : ($revenueThisMonth > 0 ? 100 : 0);

                return [
                    'rank' => $index + 1,
                    'name' => $shop->shop_name,
                    'location' => "{$shop->barangay}, {$shop->municipality}",
                    'revenue' => (float) $shop->revenue,
                    'orders' => (int) $shop->orders,
                    'growth' => $growth,
                ];
            })
            ->values();

        // Index page
        return Inertia::render('admin/analytics/Index', [
            'stats' => [
                'total_shops' => ['value' => $totalShops,     'change' => $shopsChange],
                'total_customers' => ['value' => $totalCustomers, 'change' => $customersChange],
                'total_revenue' => ['value' => $totalRevenue,   'change' => $revenueChange],
                'total_orders' => ['value' => $totalOrders,    'change' => $ordersChange],
            ],
            'revenue_chart' => $revenueChart,
            'orders_chart' => $ordersChart,
            'registrations_chart' => $registrationsChart,
            'top_shops' => $topShops,
        ]);
    }
}
