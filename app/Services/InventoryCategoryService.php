<?php

namespace App\Services;

use App\Models\InventoryCategory;
use App\Repositories\InventoryCategoryRepository;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class InventoryCategoryService
{
    public function __construct(
        private readonly InventoryCategoryRepository $categoryRepository,
    ) {}

    public function all(int $shopId): Collection
    {
        return $this->categoryRepository->allForShop($shopId);
    }

    public function archived(int $shopId): Collection
    {
        return $this->categoryRepository->archivedForShop($shopId);
    }

    public function create(int $shopId, array $data): InventoryCategory
    {
        return DB::transaction(function () use ($shopId, $data) {
            return $this->categoryRepository->createForShop($shopId, $data);
        });
    }

    public function update(InventoryCategory $category, array $data): InventoryCategory
    {
        return DB::transaction(function () use ($category, $data) {
            $this->categoryRepository->update($category, $data);
            return $category->fresh();
        });
    }

    public function delete(InventoryCategory $category): bool
    {
        return DB::transaction(function () use ($category) {
            return $this->categoryRepository->delete($category);
        });
    }

    public function restore(int $id): bool
    {
        return DB::transaction(function () use ($id) {
            return $this->categoryRepository->restore($id);
        });
    }
}
