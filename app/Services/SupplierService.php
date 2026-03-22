<?php

namespace App\Services;

use App\Models\Supplier;
use App\Repositories\SupplierRepository;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class SupplierService
{
    public function __construct(
        private readonly SupplierRepository $supplierRepository,
    ) {}

    public function paginate(int $shopId, int $perPage = 15): LengthAwarePaginator
    {
        return $this->supplierRepository->paginateForShop($shopId, $perPage);
    }

    public function archived(int $shopId): LengthAwarePaginator
    {
        return $this->supplierRepository->archivedForShop($shopId);
    }

    public function allActive(int $shopId): Collection
    {
        return $this->supplierRepository->allActiveForShop($shopId);
    }

    public function create(int $shopId, array $data): Supplier
    {
        return DB::transaction(function () use ($shopId, $data) {
            return $this->supplierRepository->createForShop($shopId, $data);
        });
    }

    public function update(Supplier $supplier, array $data): Supplier
    {
        return DB::transaction(function () use ($supplier, $data) {
            $this->supplierRepository->update($supplier, $data);
            return $supplier->fresh();
        });
    }

    public function delete(Supplier $supplier): bool
    {
        return DB::transaction(function () use ($supplier) {
            return $this->supplierRepository->delete($supplier);
        });
    }

    public function restore(int $id): bool
    {
        return DB::transaction(function () use ($id) {
            return $this->supplierRepository->restore($id);
        });
    }
}
