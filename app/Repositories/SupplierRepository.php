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
            ->withCount(['inventory', 'items'])
            ->with('latestPriceList');

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
            'items_count' => 'items_count',
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

    /** A supplier of this shop by its public id; another shop's supplier is reported as missing. */
    public function findForShopOrFail(string $publicId, int $shopId, bool $withTrashed = false): Supplier
    {
        return Supplier::query()
            ->when($withTrashed, fn (Builder $query) => $query->withTrashed())
            ->where('shop_id', $shopId)
            ->where('public_id', $publicId)
            ->firstOrFail();
    }

    /** A shop's supplier with the catalog and latest price list it has published. */
    public function detailForShopOrFail(string $publicId, int $shopId): Supplier
    {
        return Supplier::query()
            ->where('shop_id', $shopId)
            ->where('public_id', $publicId)
            ->with([
                'items' => fn ($query) => $query->orderBy('name'),
                'latestPriceList',
            ])
            ->withCount('inventory')
            ->firstOrFail();
    }

    /**
     * The active, non-archived supplier record a supplier login belongs to, while its shop is not
     * archived or disabled and still has suppliers turned on.
     */
    public function findActiveForUser(int $userId): ?Supplier
    {
        return Supplier::query()
            ->where('user_id', $userId)
            ->where('status', 'active')
            ->whereHas('shop', fn (Builder $query) => $query
                ->where('has_suppliers', true)
                ->where('status', '!=', 'disabled'))
            ->with('shop:id,shop_name')
            ->first();
    }

    public function createForShop(int $shopId, array $data): Supplier
    {
        return $this->create([...$data, 'shop_id' => $shopId]);
    }

    public function attachUser(Supplier $supplier, int $userId): void
    {
        $supplier->update(['user_id' => $userId]);
    }

    public function restore(string $publicId, int $shopId): bool
    {
        return $this->findForShopOrFail($publicId, $shopId, withTrashed: true)->restore();
    }
}
