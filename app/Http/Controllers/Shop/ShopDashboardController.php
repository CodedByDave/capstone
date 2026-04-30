<?php

namespace App\Http\Controllers\Shop;

use App\Http\Controllers\Controller;
use App\Models\Branch;
use App\Models\Employee;
use App\Models\Inventory;
use App\Models\Module;
use App\Models\InventoryMovement;
use App\Models\LowStockAlert;
use App\Models\ShopOrder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Carbon\Carbon;

class ShopDashboardController extends Controller
{
    public function index()
    {
        $user  = Auth::user();
        $shop  = $user->shop;
        $order = $user->orders()
            ->with('modules')
            ->whereIn('status', ['paid', 'approved', 'pending'])
            ->latest()
            ->first();

        // Pending order — waiting for admin review
        if ($order && $order->status === 'pending') {
            return Inertia::render('shop/Dashboard', [
                'modules'        => Module::all(),
                'order'          => null,
                'pending_order'  => [
                    'plan_name'      => $order->plan_name,
                    'billing_months' => $order->billing_months,
                    'total_price'    => $order->total_price,
                    'created_at'     => $order->created_at->format('M d, Y'),
                ],
                'approved_order' => null,
                'rejected_order' => null,
                'expired_order'  => null,
                'shop'           => null,
            ]);
        }

        // Approved non-trial, non-upgrade — payment step
        if ($order && $order->status === 'approved' && ! $order->is_trial && ! $order->is_upgrade) {
            return Inertia::render('shop/Dashboard', [
                'modules'        => Module::all(),
                'order'          => null,
                'pending_order'  => null,
                'approved_order' => [
                    'plan_name'   => $order->plan_name,
                    'total_price' => (float) $order->total_price,
                ],
                'rejected_order' => null,
                'expired_order'  => null,
                'shop'           => null,
            ]);
        }

        $trialDaysLeft = null;
        if ($order && $order->is_trial && $order->expires_at) {
            $trialDaysLeft = max(0, (int) now()->diffInDays($order->expires_at, false));
        }

        // If not paid/approved, check if expired or rejected.
        if (! $order) {
            $latestOrder = $user->orders()->latest()->first();

            // Expired subscription — show renew banner
            if ($latestOrder && $latestOrder->status === 'expired') {
                return Inertia::render('shop/Dashboard', [
                    'modules'        => Module::all(),
                    'order'          => null,
                    'rejected_order' => null,
                    'expired_order'  => [
                        'plan_name'  => $latestOrder->plan_name,
                        'expires_at' => $latestOrder->expires_at,
                        'is_trial'   => (bool) $latestOrder->is_trial,
                    ],
                    'shop' => $shop ? [
                        'shop_name'    => $shop->shop_name,
                        'phone'        => $shop->phone,
                        'block_street' => $shop->block_street,
                        'municipality' => $shop->municipality,
                        'barangay'     => $shop->barangay,
                        'postal_code'  => $shop->postal_code,
                        'status'       => $shop->status,
                    ] : null,
                ]);
            }

            $rejectedOrder = $user->orders()
                ->where('status', 'rejected')
                ->latest()
                ->first();

            $hasUsedTrial = $user->orders()->where('is_trial', true)->exists();

            return Inertia::render('shop/Dashboard', [
                'modules'        => Module::all(),
                'order'          => null,
                'has_used_trial' => $hasUsedTrial,
                'rejected_order' => $rejectedOrder ? [
                    'id'               => $rejectedOrder->id,
                    'shop_name'        => $rejectedOrder->shop_name,
                    'rejection_reason' => $rejectedOrder->rejection_reason,
                    'total_price'      => $rejectedOrder->total_price,
                    'email'            => $rejectedOrder->email,
                ] : null,
                'expired_order'  => null,
                'shop' => $shop ? [
                    'shop_name'    => $shop->shop_name,
                    'phone'        => $shop->phone,
                    'block_street' => $shop->block_street,
                    'municipality' => $shop->municipality,
                    'barangay'     => $shop->barangay,
                    'postal_code'  => $shop->postal_code,
                    'status'       => $shop->status,
                ] : null,
            ]);
        }
        // Safety: shop record might not exist yet if approval race-condition occurs
        if (! $shop) {
            return Inertia::render('shop/Dashboard', [
                'modules' => $order->modules,
                'order'   => [
                    'status'            => $order->status,
                    'shop_name'         => $order->shop_name,
                    'subscription_plan' => $order->plan_name,
                    'modules'           => $order->modules->map(fn($m) => ['name' => $m->name, 'price' => $m->price]),
                    'total_price'       => $order->total_price,
                ],
                'stats'               => null,
                'movement_chart'      => [],
                'category_breakdown'  => [],
                'employees_per_branch' => [],
                'low_stock_items'     => [],
                'recent_movements'    => [],
                'shop'                => null,
            ]);
        }

        $shopId = $shop->id;
        $now    = Carbon::now();
        $today  = $now->copy()->startOfDay();
        $thisWeekStart  = $now->copy()->startOfWeek();
        $thisMonthStart = $now->copy()->startOfMonth();
        $lastMonthStart = $now->copy()->subMonth()->startOfMonth();
        $lastMonthEnd   = $now->copy()->subMonth()->endOfMonth();

        // Total Employees
        $totalEmployees     = Employee::where('shop_id', $shopId)->count();
        $employeesActive    = Employee::where('shop_id', $shopId)->where('status', 'Active')->count();
        $employeesInactive  = $totalEmployees - $employeesActive;

        // Branches
        $totalBranches  = Branch::forShop($shopId)->count();
        $activeBranches = Branch::forShop($shopId)->active()->count();

        // Inventory stats
        $totalInventory   = Inventory::forShop($shopId)->count();
        $lowStockCount    = Inventory::forShop($shopId)->lowStock()->count();
        $outOfStockCount  = Inventory::forShop($shopId)->outOfStock()->count();
        $lowStockAlerts   = LowStockAlert::forShop($shopId)->unread()->count();

        // Inventory movements this month vs last month
        $movementsThisMonth = InventoryMovement::forShop($shopId)
            ->where('created_at', '>=', $thisMonthStart)->count();
        $movementsLastMonth = InventoryMovement::forShop($shopId)
            ->whereBetween('created_at', [$lastMonthStart, $lastMonthEnd])->count();
        $movementsChange = $movementsLastMonth > 0
            ? round((($movementsThisMonth - $movementsLastMonth) / $movementsLastMonth) * 100, 1)
            : ($movementsThisMonth > 0 ? 100 : 0);


        // INVENTORY MOVEMENT CHART — last 12 months
        $movementChart = InventoryMovement::forShop($shopId)
            ->where('created_at', '>=', $now->copy()->subMonths(11)->startOfMonth())
            ->select(
                DB::raw("DATE_FORMAT(created_at, '%b') as month"),
                DB::raw("DATE_FORMAT(created_at, '%Y-%m') as month_key"),
                DB::raw("SUM(CASE WHEN type = 'in'  THEN quantity ELSE 0 END) as stock_in"),
                DB::raw("SUM(CASE WHEN type = 'out' THEN quantity ELSE 0 END) as stock_out")
            )
            ->groupBy('month_key', 'month')
            ->orderBy('month_key')
            ->get()
            ->map(fn($r) => [
                'month'     => $r->month,
                'stock_in'  => (int) $r->stock_in,
                'stock_out' => (int) $r->stock_out,
            ])
            ->values();

        // Inventory Category Breakdown
        $categoryBreakdown = Inventory::forShop($shopId)
            ->select('inventory_categories_id', DB::raw('COUNT(*) as count'))
            ->with('category:id,name')
            ->groupBy('inventory_categories_id')
            ->get()
            ->map(fn($r) => [
                'label' => $r->category?->name ?? 'Uncategorized',
                'count' => (int) $r->count,
            ])
            ->values();

        // Employees Per Branch
        $employeesPerBranch = Branch::forShop($shopId)
            ->withCount('employees')
            ->get()
            ->map(fn($b) => [
                'branch'   => $b->name,
                'count'    => $b->employees_count,
                'status'   => $b->status,
            ])
            ->values();

        // Low Stock Item
        $lowStockItems = Inventory::forShop($shopId)
            ->lowStock()
            ->with('category:id,name')
            ->orderBy('quantity')
            ->limit(5)
            ->get()
            ->map(fn($i) => [
                'name'      => $i->name,
                'sku'       => $i->sku,
                'quantity'  => $i->quantity,
                'min_stock' => $i->min_stock,
                'status'    => $i->stock_status,
                'category'  => $i->category?->name ?? '—',
            ])
            ->values();

        // Recemt inventory movements
        $recentMovements = InventoryMovement::forShop($shopId)
            ->with('inventory:id,name,sku', 'user:id,name')
            ->latest()
            ->limit(8)
            ->get()
            ->map(fn($m) => [
                'item'      => $m->inventory?->name ?? '—',
                'sku'       => $m->inventory?->sku  ?? '—',
                'type'      => $m->type,
                'quantity'  => $m->quantity,
                'before'    => $m->quantity_before,
                'after'     => $m->quantity_after,
                'by'        => $m->user?->name ?? '—',
                'notes'     => $m->notes,
                'date'      => $m->created_at->format('M d, Y'),
            ])
            ->values();

        // ── LAUNDRY ORDER DATA ────────────────────────────────────────────────

        // Order KPIs
        $totalOrders     = ShopOrder::forShop($shopId)->count();
        $completedOrders = ShopOrder::forShop($shopId)->completed()->count();
        $pendingOrders   = ShopOrder::forShop($shopId)->pending()->count();
        $inProgressOrders = ShopOrder::forShop($shopId)->inProgress()->count();
        $completionRate  = $totalOrders > 0
            ? round(($completedOrders / $totalOrders) * 100, 1)
            : 0;
        $totalRevenue = (float) ShopOrder::forShop($shopId)
            ->where('payment_status', 'paid')
            ->sum('total_amount');
        $avgOrderValue = $completedOrders > 0
            ? round(ShopOrder::forShop($shopId)->completed()->avg('total_amount'), 2)
            : 0;
        $ordersThisMonth = ShopOrder::forShop($shopId)
            ->where('created_at', '>=', $thisMonthStart)->count();
        $ordersLastMonth = ShopOrder::forShop($shopId)
            ->whereBetween('created_at', [$lastMonthStart, $lastMonthEnd])->count();
        $ordersChange = $ordersLastMonth > 0
            ? round((($ordersThisMonth - $ordersLastMonth) / $ordersLastMonth) * 100, 1)
            : ($ordersThisMonth > 0 ? 100 : 0);

        // Monthly orders — full 12-month grid (fills zeros for months with no orders)
        $months12 = collect();
        for ($i = 11; $i >= 0; $i--) {
            $m = $now->copy()->subMonths($i)->startOfMonth();
            $months12->put($m->format('Y-m'), [
                'month'        => $m->format('M'),
                'month_key'    => $m->format('Y-m'),
                'total_orders' => 0,
                'completed'    => 0,
                'revenue'      => 0.0,
            ]);
        }
        $rawMonthlyOrders = ShopOrder::forShop($shopId)
            ->where('created_at', '>=', $now->copy()->subMonths(11)->startOfMonth())
            ->select(
                DB::raw("DATE_FORMAT(created_at, '%b') as month"),
                DB::raw("DATE_FORMAT(created_at, '%Y-%m') as month_key"),
                DB::raw("COUNT(*) as total_orders"),
                DB::raw("SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END) as completed"),
                DB::raw("COALESCE(SUM(CASE WHEN payment_status = 'paid' THEN total_amount ELSE 0 END), 0) as revenue")
            )
            ->groupBy('month_key', 'month')
            ->orderBy('month_key')
            ->get();
        foreach ($rawMonthlyOrders as $row) {
            if ($months12->has($row->month_key)) {
                $months12[$row->month_key] = [
                    'month'        => $row->month,
                    'month_key'    => $row->month_key,
                    'total_orders' => (int) $row->total_orders,
                    'completed'    => (int) $row->completed,
                    'revenue'      => (float) $row->revenue,
                ];
            }
        }
        $monthlyOrders = $months12->values();

        // Service popularity
        $servicePopularity = ShopOrder::forShop($shopId)
            ->select(
                'service_id',
                DB::raw('COUNT(*) as order_count'),
                DB::raw('COALESCE(SUM(total_amount), 0) as revenue')
            )
            ->with('service:id,service_name')
            ->groupBy('service_id')
            ->orderByDesc('order_count')
            ->limit(6)
            ->get()
            ->map(fn($r) => [
                'name'        => $r->service?->service_name ?? 'Unknown',
                'order_count' => (int) $r->order_count,
                'revenue'     => (float) $r->revenue,
            ])
            ->values();

        return Inertia::render('shop/Dashboard', [
            'modules' => $order->modules,
            'order'   => [
                'status'            => $order->status,
                'shop_name'         => $order->shop_name,
                'subscription_plan' => $order->plan_name,
                'modules'           => $order->modules->map(fn($m) => ['name' => $m->name, 'price' => $m->price]),
                'total_price'       => $order->total_price,
                'is_trial'          => (bool) $order->is_trial,
                'expires_at'        => $order->expires_at,
                'trial_days_left'   => $trialDaysLeft,
            ],
            'stats' => [
                'employees'         => ['total' => $totalEmployees,    'active' => $employeesActive,   'inactive' => $employeesInactive],
                'branches'          => ['total' => $totalBranches,     'active' => $activeBranches],
                'inventory'         => ['total' => $totalInventory,    'low_stock' => $lowStockCount,  'out_of_stock' => $outOfStockCount],
                'low_stock_alerts'  => $lowStockAlerts,
                'movements'         => ['this_month' => $movementsThisMonth, 'change' => $movementsChange],
            ],
            'movement_chart'      => $movementChart,
            'category_breakdown'  => $categoryBreakdown,
            'employees_per_branch' => $employeesPerBranch,
            'low_stock_items'     => $lowStockItems,
            'recent_movements'    => $recentMovements,

            'order_stats' => [
                'total'           => $totalOrders,
                'completed'       => $completedOrders,
                'pending'         => $pendingOrders,
                'in_progress'     => $inProgressOrders,
                'completion_rate' => $completionRate,
                'total_revenue'   => $totalRevenue,
                'avg_order_value' => (float) $avgOrderValue,
                'this_month'      => $ordersThisMonth,
                'last_month'      => $ordersLastMonth,
                'orders_change'   => $ordersChange,
            ],
            'monthly_orders'     => $monthlyOrders,
            'service_popularity' => $servicePopularity,
            'order_status_dist'  => [
                'pending'     => $pendingOrders,
                'in_progress' => $inProgressOrders,
                'completed'   => $completedOrders,
            ],

            'shop' => $shop ? [
                'shop_name'    => $shop->shop_name,
                'phone'        => $shop->phone,
                'block_street' => $shop->block_street,
                'municipality' => $shop->municipality,
                'barangay'     => $shop->barangay,
                'postal_code'  => $shop->postal_code,
                'status'       =>$shop->status,
            ] : null,
        ]);
    }
}
