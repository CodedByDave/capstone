<?php

namespace App\Http\Controllers\Shop\Finance;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use App\Models\Expense;
use App\Models\Shop;
use App\Services\ExpenseService;
use App\Services\FinanceIncomeService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Inertia\Inertia;

class FinanceDashboardController extends Controller
{
    public function __construct(
        private readonly ExpenseService $expenseService,
        private readonly FinanceIncomeService $income,
    ) {}

    private function getShop(): Shop
    {
        $user = auth()->user();

        if ($user->role === 'owner') {
            return Shop::where('owner_id', $user->id)->firstOrFail();
        }

        $employee = Employee::where('user_id', $user->id)->firstOrFail();

        return Shop::findOrFail($employee->shop_id);
    }

    public function index()
    {
        $shop = $this->getShop();

        return Inertia::render('shop/finance/Dashboard', [
            'data' => $this->expenseService->getDashboardData($shop),
        ]);
    }

    public function income(Request $request)
    {
        $shop = $this->getShop();
        $dateFrom = $request->get('date_from');
        $dateTo = $request->get('date_to');
        $method = $request->get('payment_method');

        return Inertia::render('shop/finance/Income', [
            'income' => $this->income->paginate($shop->id, $dateFrom, $dateTo, $method),
            'stats' => $this->income->stats($shop->id),
            'filters' => compact('dateFrom', 'dateTo', 'method'),
        ]);
    }

    public function transactions(Request $request)
    {
        $shop = $this->getShop();
        $dateFrom = $request->get('date_from', Carbon::now()->startOfMonth()->toDateString());
        $dateTo = $request->get('date_to', Carbon::now()->toDateString());

        // Income rows: one per payment received in the period
        $incomeRows = $this->income->transactionRows($shop->id, $dateFrom, $dateTo);

        // Expense rows
        $expenseRows = Expense::where('shop_id', $shop->id)
            ->whereBetween('expense_date', [$dateFrom, $dateTo])
            ->with('category:id,name')
            ->get()
            ->map(fn ($e) => [
                'id' => 'expense-'.$e->id,
                'type' => 'expense',
                'date' => $e->expense_date->toDateString(),
                'title' => $e->title,
                'reference' => $e->reference_number ?? '—',
                'category' => $e->category?->name ?? 'Uncategorized',
                'amount' => (float) $e->amount,
            ]);

        $transactions = $incomeRows->concat($expenseRows)
            ->sortByDesc('date')
            ->sortByDesc(fn ($r) => $r['id'])
            ->values();

        $totalIncome = $incomeRows->sum('amount');
        $totalExpenses = $expenseRows->sum('amount');

        return Inertia::render('shop/finance/Transactions', [
            'transactions' => $transactions,
            'summary' => [
                'total_income' => (float) $totalIncome,
                'total_expenses' => (float) $totalExpenses,
                'net' => (float) ($totalIncome - $totalExpenses),
            ],
            'filters' => compact('dateFrom', 'dateTo'),
        ]);
    }
}
