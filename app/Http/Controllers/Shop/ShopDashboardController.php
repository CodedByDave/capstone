<?php

namespace App\Http\Controllers\Shop;

use App\Http\Controllers\Controller;
use App\Models\Branch;
use App\Models\Employee;
use App\Models\Inventory;
use App\Models\Module;
use App\Models\InventoryMovement;
use App\Models\LowStockAlert;
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
            ->whereIn('status', ['paid', 'approved'])
            ->latest()
            ->first();

        // If not paid/approved, check if the latest order was rejected.
        if (! $order) {
            $rejectedOrder = $user->orders()
                ->where('status', 'rejected')
                ->latest()
                ->first();

            return Inertia::render('shop/Dashboard', [
                'modules'        => Module::all(),
                'order'          => null,
                'rejected_order' => $rejectedOrder ? [
                    'id'               => $rejectedOrder->id,
                    'shop_name'        => $rejectedOrder->shop_name,
                    'rejection_reason' => $rejectedOrder->rejection_reason,
                    'total_price'      => $rejectedOrder->total_price,
                    'email'            => $rejectedOrder->email,
                ] : null,
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

        return Inertia::render('shop/Dashboard', [
            'modules' => $order->modules,
            'order'   => [
                'status'            => $order->status,
                'shop_name'         => $order->shop_name,
                'subscription_plan' => $order->plan_name,
                'modules'           => $order->modules->map(fn($m) => ['name' => $m->name, 'price' => $m->price]),
                'total_price'       => $order->total_price,
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
