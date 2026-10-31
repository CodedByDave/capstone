<?php

namespace App\Repositories;

use App\Models\Expense;
use App\Models\ExpenseCategory;
use App\Models\Shop;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class ExpenseRepository
{
    public function paginate(Shop $shop, array $filters = [], int $perPage = 10): LengthAwarePaginator
    {
        return Expense::with(['category:id,name', 'creator:id,name'])
            ->where('shop_id', $shop->id)
            ->when($filters['category_id'] ?? null, fn ($q, $v) => $q->where('category_id', $v))
            ->when($filters['date_from'] ?? null, fn ($q, $v) => $q->where('expense_date', '>=', $v))
            ->when($filters['date_to'] ?? null, fn ($q, $v) => $q->where('expense_date', '<=', $v))
            ->when($filters['search'] ?? null, fn ($q, $v) => $q->where('title', 'like', "%{$v}%"))
            ->latest('expense_date')
            ->latest('id')
            ->paginate($perPage)
            ->withQueryString();
    }

    public function getArchived(Shop $shop, int $perPage = 10): LengthAwarePaginator
    {
        return Expense::onlyTrashed()
            ->with(['category:id,name', 'creator:id,name'])
            ->where('shop_id', $shop->id)
            ->latest('expense_date')
            ->latest('id')
            ->paginate($perPage);
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
