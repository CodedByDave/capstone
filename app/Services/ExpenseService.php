<?php

namespace App\Services;

use App\Models\Expense;
use App\Models\ExpenseCategory;
use App\Models\Shop;
use App\Models\ShopOrder;
use App\Repositories\ExpenseRepository;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class ExpenseService
{
    public function __construct(
        private readonly ExpenseRepository $expenseRepository,
        private readonly ActivityLogService $activityLogService,
    ) {}

    public function paginate(Shop $shop, array $filters = [], int $perPage = 10): LengthAwarePaginator
    {
        return $this->expenseRepository->paginate($shop, $filters, $perPage);
    }

    public function getArchived(Shop $shop): LengthAwarePaginator
    {
        return $this->expenseRepository->getArchived($shop);
    }

    public function getCategories(Shop $shop): Collection
    {
        return $this->expenseRepository->getCategories($shop);
    }

    public function create(Shop $shop, array $data): Expense
    {
        $expense = $this->expenseRepository->create([
            ...$data,
            'shop_id'    => $shop->id,
            'created_by' => auth()->id(),
            'updated_by' => auth()->id(),
        ]);

        $this->activityLogService->log(
            subject: $expense,
            action: 'created',
            shopId: $shop->id,
            module: 'Finance',
        );

        return $expense;
    }

    public function update(Expense $expense, array $data): Expense
    {
        $expense = $this->expenseRepository->update($expense, [
            ...$data,
            'updated_by' => auth()->id(),
        ]);

        $this->activityLogService->log(
            subject: $expense,
            action: 'updated',
            shopId: $expense->shop_id,
            module: 'Finance',
        );

        return $expense;
    }

    public function delete(Expense $expense): void
    {
        $this->activityLogService->log(
            subject: $expense,
            action: 'archived',
            shopId: $expense->shop_id,
            module: 'Finance',
        );

        $this->expenseRepository->delete($expense);
    }

    public function restore(int $id, Shop $shop): Expense
    {
        $expense = $this->expenseRepository->restore($id, $shop);

        $this->activityLogService->log(
            subject: $expense,
            action: 'restored',
            shopId: $shop->id,
            module: 'Finance',
        );

        return $expense;
    }

    public function createCategory(Shop $shop, array $data): ExpenseCategory
    {
        return $this->expenseRepository->createCategory([
            ...$data,
            'shop_id' => $shop->id,
        ]);
    }

    public function deleteCategory(Shop $shop, ExpenseCategory $category): void
    {
        abort_if($category->shop_id !== $shop->id, 403);
        $this->expenseRepository->deleteCategory($category);
    }

    public function getStats(Shop $shop): array
    {
        $now        = now();
        $monthStart = $now->copy()->startOfMonth()->toDateString();
        $monthEnd   = $now->copy()->endOfMonth()->toDateString();

        return [
            'total_expenses'      => (float) Expense::where('shop_id', $shop->id)->sum('amount'),
            'this_month_expenses' => (float) Expense::where('shop_id', $shop->id)
                ->whereBetween('expense_date', [$monthStart, $monthEnd])
                ->sum('amount'),
            'total_count'         => Expense::where('shop_id', $shop->id)->count(),
            'this_month_count'    => Expense::where('shop_id', $shop->id)
                ->whereBetween('expense_date', [$monthStart, $monthEnd])
                ->count(),
        ];
    }

    public function getDashboardData(Shop $shop): array
    {
        $now        = now();
        $monthStart = $now->copy()->startOfMonth();
        $monthEnd   = $now->copy()->endOfMonth();

        // ── Revenue from shop orders ───────────────────────────────────────────
        $revenueThisMonth = (float) ShopOrder::where('shop_id', $shop->id)
            ->whereBetween('created_at', [$monthStart, $monthEnd])
            ->sum('amount_paid');

        $totalRevenue = (float) ShopOrder::where('shop_id', $shop->id)->sum('amount_paid');

        $unpaidAmount = (float) ShopOrder::where('shop_id', $shop->id)
            ->whereIn('payment_status', ['unpaid', 'partial'])
            ->whereNull('deleted_at')
            ->selectRaw('COALESCE(SUM(total_amount - amount_paid), 0) as unpaid_total')
            ->value('unpaid_total');

        // ── Expenses ──────────────────────────────────────────────────────────
        $expensesThisMonth = (float) Expense::where('shop_id', $shop->id)
            ->whereBetween('expense_date', [$monthStart->toDateString(), $monthEnd->toDateString()])
            ->sum('amount');

        $totalExpenses = (float) Expense::where('shop_id', $shop->id)->sum('amount');

        // ── Monthly chart — last 6 months ─────────────────────────────────────
        $chartData = [];
        for ($i = 5; $i >= 0; $i--) {
            $mStart = $now->copy()->subMonths($i)->startOfMonth();
            $mEnd   = $now->copy()->subMonths($i)->endOfMonth();

            $rev = (float) ShopOrder::where('shop_id', $shop->id)
                ->whereBetween('created_at', [$mStart, $mEnd])
                ->sum('amount_paid');

            $exp = (float) Expense::where('shop_id', $shop->id)
                ->whereBetween('expense_date', [$mStart->toDateString(), $mEnd->toDateString()])
                ->sum('amount');

            $chartData[] = [
                'month'    => $mStart->format('M Y'),
                'revenue'  => $rev,
                'expenses' => $exp,
                'profit'   => $rev - $exp,
            ];
        }

        // ── Top expense categories this month ─────────────────────────────────
        $topCategories = Expense::where('shop_id', $shop->id)
            ->whereBetween('expense_date', [$monthStart->toDateString(), $monthEnd->toDateString()])
            ->with('category:id,name')
            ->selectRaw('category_id, SUM(amount) as total')
            ->groupBy('category_id')
            ->orderByDesc('total')
            ->limit(5)
            ->get()
            ->map(fn ($e) => [
                'name'  => $e->category?->name ?? 'Uncategorized',
                'total' => (float) $e->total,
            ])
            ->values()
            ->toArray();

        // ── Recent expenses ───────────────────────────────────────────────────
        $recentExpenses = Expense::where('shop_id', $shop->id)
            ->with(['category:id,name', 'creator:id,name'])
            ->latest('expense_date')
            ->latest('id')
            ->limit(5)
            ->get();

        return [
            'revenue_this_month'  => $revenueThisMonth,
            'expenses_this_month' => $expensesThisMonth,
            'profit_this_month'   => $revenueThisMonth - $expensesThisMonth,
            'total_revenue'       => $totalRevenue,
            'total_expenses'      => $totalExpenses,
            'total_profit'        => $totalRevenue - $totalExpenses,
            'unpaid_amount'       => $unpaidAmount,
            'chart_data'          => $chartData,
            'top_categories'      => $topCategories,
            'recent_expenses'     => $recentExpenses,
        ];
    }
}
