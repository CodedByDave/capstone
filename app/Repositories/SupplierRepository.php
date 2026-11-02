<?php

namespace App\Repositories;

use App\DTOs\TableSort;
use App\Models\Supplier;
use App\Repositories\Concerns\AppliesTableSort;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;

class SupplierRepository extends Repository
{
    use AppliesTableSort;

    public function __construct(Supplier $supplier)
    {
        parent::__construct($supplier);
    }

    public function paginateForShop(int $shopId, ?TableSort $sort = null, int $perPage = 15): LengthAwarePaginator
    {
        $query = $this->query()
            ->where('shop_id', $shopId)
            ->withCount('inventory');

        $this->sortSuppliers($query, $sort);

        return $query->paginate($perPage);
    }

    public function archivedForShop(int $shopId, ?TableSort $sort = null): LengthAwarePaginator
    {
        $query = Supplier::onlyTrashed()
            ->where('shop_id', $shopId)
            ->withCount('inventory');

        $this->sortSuppliers($query, $sort);

        return $query->paginate(15);
    }

    /** Sorts by a table column, falling back to the supplier name. */
    private function sortSuppliers(Builder $query, ?TableSort $sort): void
    {
        $sorted = $this->applyTableSort($query, $sort, [
            'name' => 'name',
            'contact' => 'contact_person',
            'contact_person' => 'contact_person',
            'phone' => 'phone',
            'email' => 'email',
            'status' => 'status',
            'inventory_count' => 'inventory_count',
            'deleted_at' => 'deleted_at',
        ]);

        if (! $sorted) {
            $query->orderBy('name');
        }
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
