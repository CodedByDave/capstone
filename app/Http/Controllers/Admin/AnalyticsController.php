<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Payment;
use App\Models\Shop;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Carbon\Carbon;

class AnalyticsController extends Controller
{
    public function index()
    {
        $now       = Carbon::now();
        $thisMonth = $now->copy()->startOfMonth();
        $lastMonth = $now->copy()->subMonth()->startOfMonth();
        $lastMonthEnd = $now->copy()->subMonth()->endOfMonth();

        //Stat Cards

        // Total Shops
        $totalShops     = Shop::count();
        $shopsThisMonth = Shop::where('created_at', '>=', $thisMonth)->count();
        $shopsLastMonth = Shop::whereBetween('created_at', [$lastMonth, $lastMonthEnd])->count();
        $shopsChange    = $shopsLastMonth > 0
            ? round((($shopsThisMonth - $shopsLastMonth) / $shopsLastMonth) * 100, 1)
            : ($shopsThisMonth > 0 ? 100 : 0);

        // Total Customers (role = 'user')
        $totalCustomers     = User::where('role', User::ROLE_USER)->count();
        $customersThisMonth = User::where('role', User::ROLE_USER)->where('created_at', '>=', $thisMonth)->count();
        $customersLastMonth = User::where('role', User::ROLE_USER)->whereBetween('created_at', [$lastMonth, $lastMonthEnd])->count();
        $customersChange    = $customersLastMonth > 0
            ? round((($customersThisMonth - $customersLastMonth) / $customersLastMonth) * 100, 1)
            : ($customersThisMonth > 0 ? 100 : 0);

        // Total Revenue (paid payments)
        $totalRevenue     = Payment::where('status', 'paid')->sum('amount');
        $revenueThisMonth = Payment::where('status', 'paid')->where('paid_at', '>=', $thisMonth)->sum('amount');
        $revenueLastMonth = Payment::where('status', 'paid')->whereBetween('paid_at', [$lastMonth, $lastMonthEnd])->sum('amount');
        $revenueChange    = $revenueLastMonth > 0
            ? round((($revenueThisMonth - $revenueLastMonth) / $revenueLastMonth) * 100, 1)
            : ($revenueThisMonth > 0 ? 100 : 0);

        // Total Orders (paid orders)
        $totalOrders     = Order::where('status', 'paid')->count();
        $ordersThisMonth = Order::where('status', 'paid')->where('created_at', '>=', $thisMonth)->count();
        $ordersLastMonth = Order::where('status', 'paid')->whereBetween('created_at', [$lastMonth, $lastMonthEnd])->count();
        $ordersChange    = $ordersLastMonth > 0
            ? round((($ordersThisMonth - $ordersLastMonth) / $ordersLastMonth) * 100, 1)
            : ($ordersThisMonth > 0 ? 100 : 0);

        // Revenue Chart last 12 months
        $revenueChart = Payment::where('status', 'paid')
            ->where('paid_at', '>=', $now->copy()->subMonths(11)->startOfMonth())
            ->select(
                DB::raw("DATE_FORMAT(paid_at, '%b') as month"),
                DB::raw("DATE_FORMAT(paid_at, '%Y-%m') as month_key"),
                DB::raw('SUM(amount) as amount')
            )
            ->groupBy('month_key', 'month')
            ->orderBy('month_key')
            ->get()
            ->map(fn($r) => [
                'month'  => $r->month,
                'amount' => (float) $r->amount,
            ])
            ->values();

        // Orders Chart last 12 months

        $ordersChart = Order::where('status', 'paid')
            ->where('created_at', '>=', $now->copy()->subMonths(11)->startOfMonth())
            ->select(
                DB::raw("DATE_FORMAT(created_at, '%b') as month"),
                DB::raw("DATE_FORMAT(created_at, '%Y-%m') as month_key"),
                DB::raw('COUNT(*) as count')
            )
            ->groupBy('month_key', 'month')
            ->orderBy('month_key')
            ->get()
            ->map(fn($r) => [
                'month' => $r->month,
                'count' => (int) $r->count,
            ])
            ->values();

        // Registration Chart last 12 months
        $registrationsChart = User::where('role', User::ROLE_USER)
            ->where('created_at', '>=', $now->copy()->subMonths(11)->startOfMonth())
            ->select(
                DB::raw("DATE_FORMAT(created_at, '%b') as month"),
                DB::raw("DATE_FORMAT(created_at, '%Y-%m') as month_key"),
                DB::raw('COUNT(*) as count')
            )
            ->groupBy('month_key', 'month')
            ->orderBy('month_key')
            ->get()
            ->map(fn($r) => [
                'month' => $r->month,
                'count' => (int) $r->count,
            ])
            ->values();

        // Top Performer shop
        $topShops = Shop::select(
            'shops.id',
            'shops.shop_name',
            'shops.municipality',
            'shops.barangay',
            DB::raw('COALESCE(SUM(p.amount), 0) as revenue'),
            DB::raw('COUNT(DISTINCT o.id) as orders')
        )
            ->leftJoin('orders as o', function ($join) {
                $join->on('o.user_id', '=', 'shops.owner_id')
                    ->where('o.status', '=', 'paid');
            })
            ->leftJoin('payments as p', function ($join) {
                $join->on('p.order_id', '=', 'o.id')
                    ->where('p.status', '=', 'paid');
            })
            ->groupBy('shops.id', 'shops.shop_name', 'shops.municipality', 'shops.barangay')
            ->orderByDesc('revenue')
            ->limit(5)
            ->get()
            ->map(function ($shop, $index) use ($now, $lastMonth, $lastMonthEnd) {
                // Growth: compare this month vs last month revenue per shop
                $revenueThisMonth = Payment::where('status', 'paid')
                    ->where('paid_at', '>=', $now->copy()->startOfMonth())
                    ->whereHas('order', fn($q) => $q->where('user_id', DB::table('shops')->where('id', $shop->id)->value('owner_id')))
                    ->sum('amount');

                $revenueLastMonth = Payment::where('status', 'paid')
                    ->whereBetween('paid_at', [$lastMonth, $lastMonthEnd])
                    ->whereHas('order', fn($q) => $q->where('user_id', DB::table('shops')->where('id', $shop->id)->value('owner_id')))
                    ->sum('amount');

                $growth = $revenueLastMonth > 0
                    ? round((($revenueThisMonth - $revenueLastMonth) / $revenueLastMonth) * 100, 1)
                    : ($revenueThisMonth > 0 ? 100 : 0);

                return [
                    'rank'     => $index + 1,
                    'name'     => $shop->shop_name,
                    'location' => "{$shop->barangay}, {$shop->municipality}",
                    'revenue'  => (float) $shop->revenue,
                    'orders'   => (int) $shop->orders,
                    'growth'   => $growth,
                ];
            })
            ->values();

        // Index page
        return Inertia::render('admin/analytics/Index', [
            'stats' => [
                'total_shops'     => ['value' => $totalShops,     'change' => $shopsChange],
                'total_customers' => ['value' => $totalCustomers, 'change' => $customersChange],
                'total_revenue'   => ['value' => $totalRevenue,   'change' => $revenueChange],
                'total_orders'    => ['value' => $totalOrders,    'change' => $ordersChange],
            ],
            'revenue_chart'       => $revenueChart,
            'orders_chart'        => $ordersChart,
            'registrations_chart' => $registrationsChart,
            'top_shops'           => $topShops,
        ]);
    }
}
