<?php

namespace App\Repositories;

use App\DTOs\TableSort;
use App\Models\Expense;
use App\Models\ExpenseCategory;
use App\Models\Shop;
use App\Models\User;
use App\Repositories\Concerns\AppliesTableSort;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class ExpenseRepository
{
    use AppliesTableSort;

    public function paginate(Shop $shop, array $filters = [], int $perPage = 10, ?TableSort $sort = null): LengthAwarePaginator
    {
        $query = Expense::with(['category:id,name', 'creator:id,name'])
            ->where('shop_id', $shop->id)
            ->when($filters['category_id'] ?? null, fn ($q, $v) => $q->where('category_id', $v))
            ->when($filters['date_from'] ?? null, fn ($q, $v) => $q->where('expense_date', '>=', $v))
            ->when($filters['date_to'] ?? null, fn ($q, $v) => $q->where('expense_date', '<=', $v))
            ->when($filters['search'] ?? null, fn ($q, $v) => $q->where(fn ($s) => $s
                ->where('title', 'like', "%{$v}%")
                ->orWhere('reference_number', 'like', "%{$v}%")));

        $this->sortExpenses($query, $sort);

        return $query->paginate($perPage)->withQueryString();
    }

    public function getArchived(Shop $shop, int $perPage = 10, ?TableSort $sort = null): LengthAwarePaginator
    {
        $query = Expense::onlyTrashed()
            ->with(['category:id,name', 'creator:id,name'])
            ->where('shop_id', $shop->id);

        $this->sortExpenses($query, $sort);

        return $query->paginate($perPage)->withQueryString();
    }

    /** Sorts by a table column, falling back to the newest expense date first. */
    private function sortExpenses(Builder $query, ?TableSort $sort): void
    {
        $sorted = $this->applyTableSort($query, $sort, [
            'title' => 'title',
            'expense_date' => 'expense_date',
            'reference_number' => 'reference_number',
            'amount' => 'amount',
            'category' => fn ($q, $dir) => $q->orderBy(
                ExpenseCategory::query()
                    ->select('name')
                    ->whereColumn('expense_categories.id', 'expenses.category_id'),
                $dir,
            ),
            'creator' => fn ($q, $dir) => $q->orderBy(
                User::query()
                    ->select('name')
                    ->whereColumn('users.id', 'expenses.created_by'),
                $dir,
            ),
        ]);

        if (! $sorted) {
            $query->latest('expense_date')->latest('id');
        }
    }

    public function create(array $data): Expense
    {
        return Expense::create($data);
    }

    public function update(Expense $expense, array $data): Expense
    {
        $expense->update($data);

        return $expense->fresh(['category:id,name']);
    }

    public function delete(Expense $expense): void
    {
        $expense->delete();
    }

    public function restore(string $publicId, Shop $shop): Expense
    {
        $expense = Expense::onlyTrashed()
            ->where('shop_id', $shop->id)
            ->where('public_id', $publicId)
            ->firstOrFail();

        $expense->restore();

        return $expense;
    }

    public function getCategories(Shop $shop): Collection
    {
        return ExpenseCategory::where('shop_id', $shop->id)
            ->whereNull('deleted_at')
            ->orderBy('name')
            ->get(['id', 'name', 'description']);
    }

    /** The automatic expense a record produced, if it has one. */
    public function findLinked(int $shopId, string $source, string $column, int $id): ?Expense
    {
        return Expense::where('shop_id', $shopId)
            ->where('source', $source)
            ->where($column, $id)
            ->first();
    }

    public function firstOrCreateCategory(Shop $shop, string $name, ?string $description = null): ExpenseCategory
    {
        return ExpenseCategory::firstOrCreate(
            ['shop_id' => $shop->id, 'name' => $name],
            ['description' => $description],
        );
    }

    public function createCategory(array $data): ExpenseCategory
    {
        return ExpenseCategory::create($data);
    }

    public function deleteCategory(ExpenseCategory $category): void
    {
        $category->delete();
    }
}
