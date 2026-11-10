<?php

namespace App\Repositories;

use App\Models\SupplierPriceList;
use Illuminate\Database\Eloquent\Collection;

class SupplierPriceListRepository extends Repository
{
    public function __construct(SupplierPriceList $priceList)
    {
        parent::__construct($priceList);
    }

    public function recentForSupplier(int $supplierId, int $limit = 10): Collection
    {
        return $this->query()
            ->where('supplier_id', $supplierId)
            ->latest()
            ->latest('id')
            ->limit($limit)
            ->get();
    }

    /** A price list of this supplier by public id; any other is reported as missing. */
    public function findForSupplierOrFail(string $publicId, int $supplierId): SupplierPriceList
    {
        return $this->query()
            ->where('supplier_id', $supplierId)
            ->where('public_id', $publicId)
            ->firstOrFail();
    }

    /** A price list of one of this shop's suppliers, archived suppliers included. */
    public function findForShopOrFail(string $supplierPublicId, string $publicId, int $shopId): SupplierPriceList
    {
        return $this->query()
            ->where('public_id', $publicId)
            ->whereHas('supplier', fn ($query) => $query->withTrashed()
                ->where('shop_id', $shopId)
                ->where('public_id', $supplierPublicId))
            ->firstOrFail();
    }

    public function createForSupplier(int $supplierId, array $data): SupplierPriceList
    {
        return $this->create([...$data, 'supplier_id' => $supplierId]);
    }
}
