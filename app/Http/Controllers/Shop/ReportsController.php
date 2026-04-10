<?php

namespace App\Http\Controllers\Shop;

use App\Http\Controllers\Controller;
use App\Models\Branch;
use App\Models\Employee;
use App\Models\EmployeeSchedule;
use App\Models\Inventory;
use App\Models\InventoryCategory;
use App\Models\InventoryMovement;
use App\Models\LowStockAlert;
use App\Models\Shop;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ReportsController extends Controller
{
    private function getShop(): Shop
    {
        $user = auth()->user();

        if ($user->role === 'owner') {
            return Shop::where('owner_id', $user->id)->firstOrFail();
        }

        $employee = Employee::where('user_id', $user->id)->firstOrFail();
        return Shop::findOrFail($employee->shop_id);
    }

    /** Returns the staff member's branch name, or null for owners. */
    private function getStaffBranch(): ?string
    {
        $user = auth()->user();
        if ($user->role === 'owner') return null;
        $branch = Employee::where('user_id', $user->id)->value('branch_name');
        return ($branch !== null && $branch !== '') ? $branch : null;
    }

    /** Applies a branch scope to an Employee query builder. */
    private function scopeEmployees(Shop $shop, ?string $branch)
    {
        $q = Employee::where('shop_id', $shop->id);
        if ($branch !== null) {
            $q->where('branch_name', $branch);
        }
        return $q;
    }

    private function getDateRange(string $period): array
    {
        return match ($period) {
            'today' => [Carbon::today(),               Carbon::today()->endOfDay()],
            'week'  => [Carbon::now()->startOfWeek(),  Carbon::now()->endOfWeek()],
            'month' => [Carbon::now()->startOfMonth(), Carbon::now()->endOfMonth()],
            'year'  => [Carbon::now()->startOfYear(),  Carbon::now()->endOfYear()],
            default => [Carbon::now()->startOfMonth(), Carbon::now()->endOfMonth()],
        };
    }

    public function overview(Request $request)
    {
        $shop   = $this->getShop();
        $branch = $this->getStaffBranch();
        $period = $request->get('period', 'month');
        [$from, $to] = $this->getDateRange($period);

        $totalItems      = Inventory::where('shop_id', $shop->id)->count();
        $activeItems     = Inventory::where('shop_id', $shop->id)->where('status', 'active')->count();
        $lowStockItems   = Inventory::where('shop_id', $shop->id)->whereColumn('quantity', '<=', 'min_stock')->count();
        $outOfStock      = Inventory::where('shop_id', $shop->id)->where('quantity', 0)->count();
        $totalEmployees  = $this->scopeEmployees($shop, $branch)->count();
        $totalBranches   = $branch !== null ? 1 : Branch::where('shop_id', $shop->id)->count();
        $unresolvedAlerts = LowStockAlert::where('shop_id', $shop->id)->whereIn('status', ['unread', 'read'])->count();
        $movementsInPeriod = InventoryMovement::whereHas('inventory', fn($q) => $q->where('shop_id', $shop->id))
            ->whereBetween('created_at', [$from, $to])->count();

        $movementTrend = collect(range(6, 0))->map(function ($daysAgo) use ($shop) {
            $date = Carbon::today()->subDays($daysAgo);
            return [
                'date'  => $date->format('M d'),
                'count' => InventoryMovement::whereHas('inventory', fn($q) => $q->where('shop_id', $shop->id))
                    ->whereDate('created_at', $date)->count(),
            ];
        })->values();

        $categoryBreakdown = InventoryCategory::where('shop_id', $shop->id)
            ->withCount(['inventories' => fn($q) => $q->where('status', 'active')])
            ->get()
            ->map(fn($c) => ['name' => $c->name, 'count' => $c->inventories_count]);

        return Inertia::render('shop/reports/Overview', [
            'period' => $period,
            'stats'  => [
                'total_items'        => $totalItems,
                'active_items'       => $activeItems,
                'low_stock_items'    => $lowStockItems,
                'out_of_stock'       => $outOfStock,
                'total_employees'    => $totalEmployees,
                'total_branches'     => $totalBranches,
                'unresolved_alerts'  => $unresolvedAlerts,
                'movements_period'   => $movementsInPeriod,
            ],
            'movementTrend'     => $movementTrend,
            'categoryBreakdown' => $categoryBreakdown,
        ]);
    }

    public function inventory(Request $request)
    {
        $shop   = $this->getShop();
        $period = $request->get('period', 'month');
        [$from, $to] = $this->getDateRange($period);

        $totalItems    = Inventory::where('shop_id', $shop->id)->count();
        $activeItems   = Inventory::where('shop_id', $shop->id)->where('status', 'active')->count();
        $inactiveItems = Inventory::where('shop_id', $shop->id)->where('status', 'inactive')->count();
        $lowStock      = Inventory::where('shop_id', $shop->id)->whereColumn('quantity', '<=', 'min_stock')->where('quantity', '>', 0)->count();
        $outOfStock    = Inventory::where('shop_id', $shop->id)->where('quantity', 0)->count();

        $topStocked = Inventory::where('shop_id', $shop->id)
            ->where('status', 'active')
            ->orderByDesc('quantity')
            ->limit(5)
            ->get(['id', 'name', 'quantity', 'unit', 'min_stock']);

        $lowStockItems = Inventory::where('shop_id', $shop->id)
            ->whereColumn('quantity', '<=', 'min_stock')
            ->orderBy('quantity')
            ->limit(5)
            ->get(['id', 'name', 'quantity', 'unit', 'min_stock']);

        $movementsByType = InventoryMovement::whereHas('inventory', fn($q) => $q->where('shop_id', $shop->id))
            ->whereBetween('created_at', [$from, $to])
            ->selectRaw('type, count(*) as count')
            ->groupBy('type')
            ->get()
            ->map(fn($m) => ['type' => $m->type, 'count' => $m->count]);

        $movementTrend = collect(range(6, 0))->map(function ($daysAgo) use ($shop) {
            $date = Carbon::today()->subDays($daysAgo);
            $restock = InventoryMovement::whereHas('inventory', fn($q) => $q->where('shop_id', $shop->id))
                ->whereDate('created_at', $date)->where('type', 'restock')->count();
            $usage = InventoryMovement::whereHas('inventory', fn($q) => $q->where('shop_id', $shop->id))
                ->whereDate('created_at', $date)->where('type', 'usage')->count();
            return [
                'date'    => $date->format('M d'),
                'restock' => $restock,
                'usage'   => $usage,
            ];
        })->values();

        $categoryBreakdown = InventoryCategory::where('shop_id', $shop->id)
            ->withCount(['inventories' => fn($q) => $q->where('status', 'active')])
            ->withSum(['inventories' => fn($q) => $q->where('status', 'active')], 'quantity')
            ->get()
            ->map(fn($c) => [
                'name'     => $c->name,
                'count'    => $c->inventories_count,
                'quantity' => $c->inventories_sum_quantity ?? 0,
            ]);

        $supplierBreakdown = Inventory::where('shop_id', $shop->id)
            ->with('supplier:id,name')
            ->whereNotNull('supplier_id')
            ->selectRaw('supplier_id, count(*) as count')
            ->groupBy('supplier_id')
            ->get()
            ->map(fn($i) => [
                'name'  => $i->supplier?->name ?? 'Unknown',
                'count' => $i->count,
            ]);

        return Inertia::render('shop/reports/Inventory', [
            'period' => $period,
            'stats'  => [
                'total_items'    => $totalItems,
                'active_items'   => $activeItems,
                'inactive_items' => $inactiveItems,
                'low_stock'      => $lowStock,
                'out_of_stock'   => $outOfStock,
            ],
            'topStocked'        => $topStocked,
            'lowStockItems'     => $lowStockItems,
            'movementsByType'   => $movementsByType,
            'movementTrend'     => $movementTrend,
            'categoryBreakdown' => $categoryBreakdown,
            'supplierBreakdown' => $supplierBreakdown,
        ]);
    }

    public function employee(Request $request)
    {
        $shop   = $this->getShop();
        $branch = $this->getStaffBranch();
        $period = $request->get('period', 'month');
        [$from, $to] = $this->getDateRange($period);

        $totalEmployees = $this->scopeEmployees($shop, $branch)->count();
        $totalBranches  = $branch !== null ? 1 : Branch::where('shop_id', $shop->id)->count();
        $newEmployees   = $this->scopeEmployees($shop, $branch)->whereBetween('created_at', [$from, $to])->count();

        $employeesByBranch = $branch !== null
            ? collect([['name' => $branch, 'count' => $this->scopeEmployees($shop, $branch)->count()]])
            : Branch::where('shop_id', $shop->id)
                ->withCount('employees')
                ->get()
                ->map(fn($b) => ['name' => $b->name, 'count' => $b->employees_count]);

        $employeeGrowth = collect(range(5, 0))->map(function ($monthsAgo) use ($shop, $branch) {
            $date = Carbon::now()->subMonths($monthsAgo);
            return [
                'month' => $date->format('M Y'),
                'count' => $this->scopeEmployees($shop, $branch)
                    ->whereYear('created_at', $date->year)
                    ->whereMonth('created_at', $date->month)
                    ->count(),
            ];
        })->values();

        $scheduledThisWeek = EmployeeSchedule::whereHas(
            'employee',
            fn($q) => $q->where('shop_id', $shop->id)->when($branch, fn($q) => $q->where('branch_name', $branch))
        )
            ->whereBetween('date', [Carbon::now()->startOfWeek(), Carbon::now()->endOfWeek()])
            ->count();

        $recentEmployees = $this->scopeEmployees($shop, $branch)
            ->latest()
            ->limit(5)
            ->get(['id', 'first_name', 'last_name', 'email', 'branch_name', 'created_at'])
            ->map(fn($e) => [
                'name'       => "{$e->first_name} {$e->last_name}",
                'email'      => $e->email,
                'branch'     => $e->branch_name ?? '—',
                'created_at' => $e->created_at->format('M d, Y'),
            ]);

        $employeesByRole = $this->scopeEmployees($shop, $branch)
            ->with('roles')
            ->get()
            ->flatMap(fn($e) => $e->roles->pluck('role'))
            ->countBy()
            ->map(fn($count, $role) => ['role' => $role, 'count' => $count])
            ->values();

        return Inertia::render('shop/reports/Employee', [
            'period' => $period,
            'stats'  => [
                'total_employees'    => $totalEmployees,
                'total_branches'     => $totalBranches,
                'new_employees'      => $newEmployees,
                'scheduled_week'     => $scheduledThisWeek,
            ],
            'employeesByBranch' => $employeesByBranch,
            'employeeGrowth'    => $employeeGrowth,
            'recentEmployees'   => $recentEmployees,
            'employeesByRole'   => $employeesByRole,
        ]);
    }
}
