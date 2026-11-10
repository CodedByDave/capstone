<?php

namespace App\Repositories;

use App\DTOs\TableSort;
use App\Models\Supplier;
use App\Models\SupplierItem;
use App\Repositories\Concerns\AppliesTableSort;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class SupplierItemRepository extends Repository
{
    use AppliesTableSort;

    public function __construct(SupplierItem $item)
    {
        parent::__construct($item);
    }

    /** One page of a supplier's catalog, optionally searched and filtered by stock. */
    public function paginateForSupplier(
        int $supplierId,
        string $search = '',
        string $stock = '',
        ?TableSort $sort = null,
        int $perPage = 10,
    ): LengthAwarePaginator {
        $term = '%'.addcslashes($search, '%_\\').'%';
        $query = $this->query()
            ->where('supplier_id', $supplierId)
            ->when($search !== '', fn ($query) => $query->where(function ($query) use ($term) {
                $query->where('name', 'like', $term)->orWhere('unit', 'like', $term);
            }))
            ->when($stock === 'in_stock', fn ($query) => $query->where('stock_quantity', '>', 0))
            ->when($stock === 'out_of_stock', fn ($query) => $query->where('stock_quantity', '<=', 0));

        $sorted = $this->applyTableSort($query, $sort, [
            'name' => 'name',
            'unit' => 'unit',
            'stock_quantity' => 'stock_quantity',
            'unit_price' => 'unit_price',
            'updated_at' => 'updated_at',
        ]);

        if (! $sorted) {
            $query->orderBy('name');
        }

        return $query->paginate($perPage)->withQueryString();
    }

    /** @return array{total: int, in_stock: int, out_of_stock: int} */
    public function stockCountsForSupplier(int $supplierId): array
    {
        $row = $this->query()
            ->where('supplier_id', $supplierId)
            ->selectRaw('COUNT(*) as total')
            ->selectRaw('SUM(CASE WHEN stock_quantity > 0 THEN 1 ELSE 0 END) as in_stock')
            ->first();

        $total = (int) ($row->total ?? 0);
        $inStock = (int) ($row->in_stock ?? 0);

        return ['total' => $total, 'in_stock' => $inStock, 'out_of_stock' => $total - $inStock];
    }

    /** @return Collection<int, array{public_id: string, name: string, unit_price: string}> */
    public function priceRowsForSupplier(int $supplierId): Collection
    {
        return $this->query()
            ->where('supplier_id', $supplierId)
            ->orderBy('name')
            ->get(['public_id', 'name', 'unit_price'])
            ->map(fn (SupplierItem $item) => [
                'public_id' => $item->public_id,
                'name' => $item->name,
                'unit_price' => (string) $item->unit_price,
            ]);
    }

    /** An item of this supplier by public id; another supplier's item is reported as missing. */
    public function findForSupplierOrFail(string $publicId, int $supplierId): SupplierItem
    {
        return $this->query()
            ->where('supplier_id', $supplierId)
            ->where('public_id', $publicId)
            ->firstOrFail();
    }

    public function createForSupplier(Supplier $supplier, array $data): SupplierItem
    {
        return $this->create([...$data, 'supplier_id' => $supplier->id]);
    }
}
