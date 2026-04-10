<?php

namespace App\Http\Controllers\Shop\Finance;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use App\Models\Expense;
use App\Models\ExpenseCategory;
use App\Models\Shop;
use App\Services\ExpenseService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ExpenseController extends Controller
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

    private function base(): string
    {
        return auth()->user()->role === 'owner' ? '/shop' : '/staff';
    }

    public function index(Request $request)
    {
        $shop    = $this->getShop();
        $filters = $request->only(['search', 'category_id', 'date_from', 'date_to']);

        return Inertia::render('shop/finance/expenses/Index', [
            'expenses'   => $this->expenseService->paginate($shop, $filters),
            'stats'      => $this->expenseService->getStats($shop),
            'categories' => $this->expenseService->getCategories($shop),
            'filters'    => $filters,
        ]);
    }

    public function archive()
    {
        $shop = $this->getShop();

        return Inertia::render('shop/finance/expenses/Archive', [
            'expenses' => $this->expenseService->getArchived($shop),
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'title'            => ['required', 'string', 'max:255'],
            'amount'           => ['required', 'numeric', 'min:0.01'],
            'expense_date'     => ['required', 'date'],
            'category_id'      => ['nullable', 'integer'],
            'description'      => ['nullable', 'string', 'max:1000'],
            'reference_number' => ['nullable', 'string', 'max:100'],
        ]);

        $shop = $this->getShop();
        $this->expenseService->create($shop, $data);

        return back()->with('toast', ['type' => 'success', 'message' => 'Expense recorded successfully.']);
    }

    public function update(Request $request, Expense $expense)
    {
        $shop = $this->getShop();
        abort_if($expense->shop_id !== $shop->id, 403);

        $data = $request->validate([
            'title'            => ['required', 'string', 'max:255'],
            'amount'           => ['required', 'numeric', 'min:0.01'],
            'expense_date'     => ['required', 'date'],
            'category_id'      => ['nullable', 'integer'],
            'description'      => ['nullable', 'string', 'max:1000'],
            'reference_number' => ['nullable', 'string', 'max:100'],
        ]);

        $this->expenseService->update($expense, $data);

        return back()->with('toast', ['type' => 'success', 'message' => 'Expense updated successfully.']);
    }

    public function destroy(Expense $expense)
    {
        $shop = $this->getShop();
        abort_if($expense->shop_id !== $shop->id, 403);

        $this->expenseService->delete($expense);

        return back()->with('toast', ['type' => 'success', 'message' => 'Expense archived.']);
    }

    public function restore(int $id)
    {
        $shop = $this->getShop();
        $this->expenseService->restore($id, $shop);

        return back()->with('toast', ['type' => 'success', 'message' => 'Expense restored.']);
    }

    public function storeCategory(Request $request)
    {
        $request->validate([
            'name'        => ['required', 'string', 'max:100'],
            'description' => ['nullable', 'string', 'max:255'],
        ]);

        $shop = $this->getShop();

        $exists = ExpenseCategory::where('shop_id', $shop->id)
            ->where('name', $request->name)
            ->whereNull('deleted_at')
            ->exists();

        if ($exists) {
            return back()->withErrors(['category_name' => 'Category already exists.']);
        }

        $category = $this->expenseService->createCategory($shop, $request->only('name', 'description'));

        return back()->with('toast', ['type' => 'success', 'message' => "Category \"{$category->name}\" created."]);
    }

    public function destroyCategory(ExpenseCategory $category)
    {
        $shop = $this->getShop();
        $this->expenseService->deleteCategory($shop, $category);

        return back()->with('toast', ['type' => 'success', 'message' => 'Category deleted.']);
    }
}
