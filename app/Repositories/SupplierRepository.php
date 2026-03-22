<?php

namespace App\Repositories;

use App\Models\Supplier;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

class SupplierRepository extends Repository
{
    public function __construct(Supplier $supplier)
    {
        parent::__construct($supplier);
    }

    public function paginateForShop(int $shopId, int $perPage = 15): LengthAwarePaginator
    {
        return $this->query()
            ->where('shop_id', $shopId)
            ->withCount('inventory')
            ->orderBy('name')
            ->paginate($perPage);
    }

    public function archivedForShop(int $shopId): LengthAwarePaginator
    {
        return Supplier::onlyTrashed()
            ->where('shop_id', $shopId)
            ->withCount('inventory')
            ->orderBy('name')
            ->paginate(15);
    }

    public function allActiveForShop(int $shopId): Collection
    {
        return $this->query()
            ->where('shop_id', $shopId)
            ->where('status', 'active')
            ->orderBy('name')
            ->get();
    }

    public function createForShop(int $shopId, array $data): Supplier
    {
        return $this->create([...$data, 'shop_id' => $shopId]);
    }

    public function restore(int $id): bool
    {
        return Supplier::withTrashed()->findOrFail($id)->restore();
    }
}
