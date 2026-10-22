<?php

namespace App\Repositories;

use App\Models\Shop;
use App\Models\ShopOrder;
use App\Models\ShopService;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

class ShopOrderRepository
{
    public function __construct(protected ShopOrder $model) {}

    public function paginateByShop(int $shopId, int $perPage = 15, array $filters = [], bool $onlyTrashed = false, ?string $branch = null): LengthAwarePaginator
    {
        $query = $this->model->newQuery()
            ->with(['service', 'supplies'])
            ->when($onlyTrashed, fn ($q) => $q->onlyTrashed())
            ->forShop($shopId)
            ->when($branch !== null, fn ($q) => $q->where(function ($q) use ($branch) {
                $q->where('branch_name', $branch)
                    ->orWhereNull('branch_name')
                    ->orWhere('branch_name', '');
            }));

        if (! empty($filters['status']) && ! $onlyTrashed) {
            $query->where('status', $filters['status']);
        }

        if (! empty($filters['payment_status']) && ! $onlyTrashed) {
            $query->where('payment_status', $filters['payment_status']);
        }

        if (filled($filters['search'] ?? null)) {
            $like = '%'.addcslashes((string) $filters['search'], '%_\\').'%';

            $query->where(function ($q) use ($like) {
                $q->where('customer_name', 'like', $like)
                    ->orWhere('order_number', 'like', $like)
                    ->orWhere('customer_phone', 'like', $like);
            });
        }

        $this->applySorting($query, $filters['sort_by'] ?? 'created_at', $filters['sort_direction'] ?? 'desc');

        return $query->paginate($perPage)->withQueryString();
    }

    /** Sorts on a whitelisted column, newest first when nothing valid is asked for. */
    private function applySorting($query, string $sortBy, string $direction): void
    {
        $direction = $direction === 'asc' ? 'asc' : 'desc';

        $columns = [
            'order_number' => 'order_number',
            'customer' => 'customer_name',
            'status' => 'status',
            'payment_status' => 'payment_status',
            'total' => 'total_amount',
            'created_at' => 'created_at',
        ];

        if ($sortBy === 'service') {
            // A subquery, not a join, so the shop scope and soft-delete filters stay unambiguous.
            $query->orderBy(
                ShopService::query()
                    ->select('service_name')
                    ->whereColumn('services_and_pricing.id', 'shop_orders.service_id'),
                $direction,
            );
        } else {
            $query->orderBy($columns[$sortBy] ?? 'created_at', $direction);
        }

        // A stable tie-break so rows never jump between pages.
        $query->orderByDesc('id');
    }

    public function findById(int $id): ?ShopOrder
    {
        return $this->model->with(['service', 'supplies'])->find($id);
    }

    public function findForCustomerOnlinePayment(string $paymentPublicId, int $customerId): ?ShopOrder
    {
        return $this->model->newQuery()
            ->with(['shop', 'customer'])
            ->where('payment_public_id', $paymentPublicId)
            ->where('user_id', $customerId)
            ->first();
    }

    /** The customer's own order with what is needed to decide whether it can be rated. */
    public function findOwnedForRating(string $paymentPublicId, int $customerId): ?ShopOrder
    {
        return $this->model->newQuery()
            ->with(['delivery', 'rating', 'shop:id,shop_name'])
            ->where('payment_public_id', $paymentPublicId)
            ->where('user_id', $customerId)
            ->first();
    }

    /**
     * The customer's most recent completed orders that have no rating yet. The rating
     * rules (delivery state, time window) are applied by the caller.
     */
    public function completedWithoutRating(int $customerId, int $limit = 25): Collection
    {
        return $this->model->newQuery()
            ->with(['delivery', 'rating', 'shop:id,shop_name'])
            ->where('user_id', $customerId)
            ->where('status', 'completed')
            ->doesntHave('rating')
            ->latest('updated_at')
            ->limit($limit)
            ->get();
    }

    /** Whether the shop completed any order since the given time. */
    public function hasCompletedSince(int $shopId, \DateTimeInterface $since): bool
    {
        return $this->model->newQuery()
            ->where('shop_id', $shopId)
            ->where('status', 'completed')
            ->where('completed_at', '>=', $since)
            ->exists();
    }

    public function findForCustomerDelivery(string $paymentPublicId, int $customerId): ?ShopOrder
    {
        return $this->model->newQuery()
            ->with(['shop.owner', 'delivery'])
            ->where('payment_public_id', $paymentPublicId)
            ->where('user_id', $customerId)
            ->first();
    }

    /**
     * Re-read the order with a row lock so concurrent delivery requests cannot both pass the eligibility check.
     */
    public function lockForUpdate(ShopOrder $order): ShopOrder
    {
        return $this->model->newQuery()->whereKey($order->getKey())->lockForUpdate()->firstOrFail();
    }

    public function findForCustomerReceipt(string $paymentPublicId, int $customerId): ?ShopOrder
    {
        return $this->model->newQuery()
            ->with(['shop', 'service'])
            ->where('payment_public_id', $paymentPublicId)
            ->where('user_id', $customerId)
            ->first();
    }

    public function findByOrderNumber(string $orderNumber): ?ShopOrder
    {
        return $this->model->where('order_number', $orderNumber)->first();
    }

    public function create(array $data): ShopOrder
    {
        return $this->model->create($data);
    }

    public function findActiveShop(int $shopId): ?Shop
    {
        return Shop::query()->whereKey($shopId)->where('status', 'active')->first();
    }

    public function findActiveServiceForShop(int $serviceId, int $shopId): ?ShopService
    {
        return ShopService::query()
            ->whereKey($serviceId)
            ->where('shop_id', $shopId)
            ->where('is_active', true)
            ->first();
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
        $sync = collect($supplies)->keyBy('inventory_id')->map(fn ($item) => [
            'quantity_used' => $item['quantity_used'],
            'unit' => $item['unit'] ?? null,
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
            ->when($branch !== null, fn ($q) => $q->where(function ($q) use ($branch) {
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
