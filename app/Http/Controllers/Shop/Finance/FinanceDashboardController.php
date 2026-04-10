<?php

namespace App\Http\Controllers\Shop\Finance;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use App\Models\Expense;
use App\Models\Shop;
use App\Models\ShopOrder;
use App\Services\ExpenseService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Inertia\Inertia;

class FinanceDashboardController extends Controller
{
    public function __construct(
        private readonly ExpenseService $expenseService,
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
        $shop     = $this->getShop();
        $dateFrom = $request->get('date_from');
        $dateTo   = $request->get('date_to');
        $method   = $request->get('payment_method');

        $query = ShopOrder::where('shop_id', $shop->id)
            ->where('amount_paid', '>', 0)
            ->with('service:id,service_name')
            ->when($dateFrom, fn ($q) => $q->whereDate('paid_at', '>=', $dateFrom))
            ->when($dateTo,   fn ($q) => $q->whereDate('paid_at', '<=', $dateTo))
            ->when($method,   fn ($q) => $q->where('payment_method', $method))
            ->latest('paid_at')
            ->latest('id');

        $income = $query->paginate(15)->withQueryString();

        $now        = Carbon::now();
        $monthStart = $now->copy()->startOfMonth()->toDateString();
        $monthEnd   = $now->copy()->endOfMonth()->toDateString();

        $stats = [
            'total_income'      => (float) ShopOrder::where('shop_id', $shop->id)->sum('amount_paid'),
            'this_month_income' => (float) ShopOrder::where('shop_id', $shop->id)
                ->whereBetween('paid_at', [$monthStart . ' 00:00:00', $monthEnd . ' 23:59:59'])
                ->sum('amount_paid'),
            'total_count'       => ShopOrder::where('shop_id', $shop->id)->where('amount_paid', '>', 0)->count(),
            'unpaid_count'      => ShopOrder::where('shop_id', $shop->id)->where('payment_status', 'unpaid')->count(),
        ];

        return Inertia::render('shop/finance/Income', [
            'income'  => $income,
            'stats'   => $stats,
            'filters' => compact('dateFrom', 'dateTo', 'method'),
        ]);
    }

    public function transactions(Request $request)
    {
        $shop     = $this->getShop();
        $dateFrom = $request->get('date_from', Carbon::now()->startOfMonth()->toDateString());
        $dateTo   = $request->get('date_to',   Carbon::now()->toDateString());

        // Income rows from paid/partial shop orders
        $incomeRows = ShopOrder::where('shop_id', $shop->id)
            ->where('amount_paid', '>', 0)
            ->whereBetween('paid_at', [$dateFrom . ' 00:00:00', $dateTo . ' 23:59:59'])
            ->with('service:id,service_name')
            ->get()
            ->map(fn ($o) => [
                'id'          => 'income-' . $o->id,
                'type'        => 'income',
                'date'        => $o->paid_at ? $o->paid_at->toDateString() : $o->created_at->toDateString(),
                'title'       => $o->customer_name . ' — ' . ($o->service?->service_name ?? 'Order'),
                'reference'   => $o->order_number,
                'category'    => $o->payment_method ?? '—',
                'amount'      => (float) $o->amount_paid,
            ]);

        // Expense rows
        $expenseRows = Expense::where('shop_id', $shop->id)
            ->whereBetween('expense_date', [$dateFrom, $dateTo])
            ->with('category:id,name')
            ->get()
            ->map(fn ($e) => [
                'id'          => 'expense-' . $e->id,
                'type'        => 'expense',
                'date'        => $e->expense_date->toDateString(),
                'title'       => $e->title,
                'reference'   => $e->reference_number ?? '—',
                'category'    => $e->category?->name ?? 'Uncategorized',
                'amount'      => (float) $e->amount,
            ]);

        $transactions = $incomeRows->concat($expenseRows)
            ->sortByDesc('date')
            ->sortByDesc(fn ($r) => $r['id'])
            ->values();

        $totalIncome   = $incomeRows->sum('amount');
        $totalExpenses = $expenseRows->sum('amount');

        return Inertia::render('shop/finance/Transactions', [
            'transactions'  => $transactions,
            'summary' => [
                'total_income'   => (float) $totalIncome,
                'total_expenses' => (float) $totalExpenses,
                'net'            => (float) ($totalIncome - $totalExpenses),
            ],
            'filters' => compact('dateFrom', 'dateTo'),
        ]);
    }
}

