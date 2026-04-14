<?php

namespace App\Repositories;

use App\Models\ShopOrder;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

class ShopOrderRepository
{
    public function __construct(protected ShopOrder $model) {}

    public function paginateByShop(int $shopId, int $perPage = 15, array $filters = [], bool $onlyTrashed = false, ?string $branch = null): LengthAwarePaginator
    {
        $query = $this->model->newQuery()
            ->with(['service', 'supplies'])
            ->when($onlyTrashed, fn($q) => $q->onlyTrashed())
            ->forShop($shopId)
            ->when($branch !== null, fn($q) => $q->where(function ($q) use ($branch) {
                $q->where('branch_name', $branch)
                  ->orWhereNull('branch_name')
                  ->orWhere('branch_name', '');
            }))
            ->latest();

        if (!empty($filters['status']) && !$onlyTrashed) {
            $query->where('status', $filters['status']);
        }

        if (!empty($filters['payment_status']) && !$onlyTrashed) {
            $query->where('payment_status', $filters['payment_status']);
        }

        if (!empty($filters['search'])) {
            $query->where(function ($q) use ($filters) {
                $q->where('customer_name', 'like', "%{$filters['search']}%")
                    ->orWhere('order_number', 'like', "%{$filters['search']}%")
                    ->orWhere('customer_phone', 'like', "%{$filters['search']}%");
            });
        }

        return $query->paginate($perPage);
    }

    public function findById(int $id): ?ShopOrder
    {
        return $this->model->with(['service', 'supplies'])->find($id);
    }

    public function findByOrderNumber(string $orderNumber): ?ShopOrder
    {
        return $this->model->where('order_number', $orderNumber)->first();
    }

    public function create(array $data): ShopOrder
    {
        return $this->model->create($data);
    }

    public function update(ShopOrder $order, array $data): ShopOrder
    {
        $order->update($data);

        return $order->fresh(['service', 'supplies']);
    }

    public function delete(ShopOrder $order): bool
    {
        return $order->delete();
    }

    public function syncSupplies(ShopOrder $order, array $supplies): void
    {
        // $supplies = [['inventory_id' => 1, 'quantity_used' => 2, 'unit' => 'sachet'], ...]
        $sync = collect($supplies)->keyBy('inventory_id')->map(fn($item) => [
            'quantity_used'         => $item['quantity_used'],
            'unit'                  => $item['unit'] ?? null,
            'inventory_movement_id' => $item['inventory_movement_id'] ?? null,
        ])->all();

        $order->supplies()->sync($sync);
    }

    public function getByShop(int $shopId): Collection
    {
        return $this->model->forShop($shopId)->with(['service', 'supplies'])->latest()->get();
    }

    public function countByStatus(int $shopId, ?string $branch = null): array
    {
        return $this->model->forShop($shopId)
            ->when($branch !== null, fn($q) => $q->where(function ($q) use ($branch) {
                $q->where('branch_name', $branch)
                  ->orWhereNull('branch_name')
                  ->orWhere('branch_name', '');
            }))
            ->selectRaw('status, COUNT(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status')
            ->all();
    }
}
