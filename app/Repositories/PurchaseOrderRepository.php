<?php

namespace App\Repositories;

use App\DTOs\TableSort;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;
use App\Models\Supplier;
use App\Models\SupplierItem;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;

class PurchaseOrderRepository
{
    private const SORTABLE = [
        'po_number' => 'po_number',
        'supplier' => 'suppliers.name',
        'status' => 'status',
        'total' => 'total',
        'expected_date' => 'expected_date',
        'created_at' => 'purchase_orders.created_at',
    ];

    /** A shop's purchase orders, newest first unless a sortable column is asked for. */
    public function paginateForShop(int $shopId, string $search, string $status, TableSort $sort, int $perPage): LengthAwarePaginator
    {
        $query = PurchaseOrder::query()
            ->select('purchase_orders.*')
            ->join('suppliers', 'suppliers.id', '=', 'purchase_orders.supplier_id')
            ->where('purchase_orders.shop_id', $shopId)
            ->with('supplier:id,public_id,name')
            ->withCount('items')
            ->when($status !== '', fn (Builder $q) => $q->where('purchase_orders.status', $status))
            ->when($search !== '', fn (Builder $q) => $q->where(fn (Builder $inner) => $inner
                ->where('purchase_orders.po_number', 'like', "%{$search}%")
                ->orWhere('suppliers.name', 'like', "%{$search}%")));

        $column = self::SORTABLE[$sort->by ?? ''] ?? null;
        $column
            ? $query->orderBy($column, $sort->descending() ? 'desc' : 'asc')
            : $query->latest('purchase_orders.created_at');

        return $query->paginate($perPage)->withQueryString();
    }

    /** @return array<string, int> */
    public function statusCountsForShop(int $shopId): array
    {
        return PurchaseOrder::query()
            ->where('shop_id', $shopId)
            ->selectRaw('status, count(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status')
            ->map(fn ($count) => (int) $count)
            ->all();
    }

    /** A purchase order of this shop; another shop's order is reported as missing. */
    public function findForShopOrFail(string $publicId, int $shopId): PurchaseOrder
    {
        return PurchaseOrder::query()
            ->where('shop_id', $shopId)
            ->where('public_id', $publicId)
            ->with(['supplier:id,public_id,name,contact_person,phone,email,user_id', 'items.inventory:id,name,unit,quantity', 'creator:id,name', 'receiver:id,name'])
            ->firstOrFail();
    }

    /** The shop's active suppliers with the supplies each one sells, for building an order. */
    public function catalogForShop(int $shopId): Collection
    {
        return Supplier::query()
            ->where('shop_id', $shopId)
            ->where('status', 'active')
            ->with(['items' => fn ($q) => $q->orderBy('name')])
            ->orderBy('name')
            ->get(['id', 'public_id', 'name', 'lead_time_days']);
    }

    /** An active supplier of this shop; another shop's supplier is reported as missing. */
    public function activeSupplierForShopOrFail(string $publicId, int $shopId): Supplier
    {
        return Supplier::query()
            ->where('shop_id', $shopId)
            ->where('public_id', $publicId)
            ->where('status', 'active')
            ->firstOrFail();
    }

    /** @return Collection<int, SupplierItem> The supplier's items with these public ids, keyed by public id. */
    public function supplierItems(int $supplierId, array $publicIds): Collection
    {
        return SupplierItem::query()
            ->where('supplier_id', $supplierId)
            ->whereIn('public_id', $publicIds)
            ->get()
            ->keyBy('public_id');
    }

    public function createWithItems(array $order, array $items): PurchaseOrder
    {
        $purchaseOrder = PurchaseOrder::create($order);
        $purchaseOrder->items()->createMany($items);

        return $purchaseOrder;
    }

    public function lockForUpdate(PurchaseOrder $order): PurchaseOrder
    {
        return PurchaseOrder::query()->whereKey($order->getKey())->lockForUpdate()->firstOrFail();
    }

    public function update(PurchaseOrder $order, array $data): PurchaseOrder
    {
        $order->update($data);

        return $order;
    }

    public function setReceivedQuantity(PurchaseOrderItem $item, float $quantity): void
    {
        $item->update(['received_quantity' => $quantity]);
    }

    /** Orders a supplier has been sent (never the shop's drafts), newest first. */
    public function forSupplier(int $supplierId, int $limit = 50): Collection
    {
        return PurchaseOrder::query()
            ->where('supplier_id', $supplierId)
            ->where('status', '!=', PurchaseOrder::STATUS_DRAFT)
            ->with('items:id,purchase_order_id,name,unit,quantity,unit_price,line_total')
            ->latest('sent_at')
            ->limit($limit)
            ->get();
    }

    /** One order sent to this supplier; drafts and other suppliers' orders are reported as missing. */
    public function findForSupplierOrFail(string $publicId, int $supplierId): PurchaseOrder
    {
        return PurchaseOrder::query()
            ->where('supplier_id', $supplierId)
            ->where('public_id', $publicId)
            ->where('status', '!=', PurchaseOrder::STATUS_DRAFT)
            ->firstOrFail();
    }
}
