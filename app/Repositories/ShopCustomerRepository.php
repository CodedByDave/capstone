<?php

namespace App\Repositories;

use App\Models\ShopCustomer;
use App\Models\ShopOrder;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\ModelNotFoundException;

/** Every query on a shop's customer records. All of them are limited to one shop. */
class ShopCustomerRepository
{
    public function findByIdentity(int $shopId, string $identityKey): ?ShopCustomer
    {
        return ShopCustomer::query()->forShop($shopId)->where('identity_key', $identityKey)->first();
    }

    /**
     * @throws ModelNotFoundException for a customer of another shop, reported like a missing one
     */
    public function findByPublicIdForShop(string $publicId, int $shopId): ShopCustomer
    {
        return ShopCustomer::query()->forShop($shopId)->where('public_id', $publicId)->firstOrFail();
    }

    public function create(array $data): ShopCustomer
    {
        return ShopCustomer::create($data);
    }

    public function update(ShopCustomer $customer, array $data): ShopCustomer
    {
        $customer->update($data);

        return $customer;
    }

    /**
     * Customers whose name or number contains the typed text, for the suggestions on the order form.
     *
     * @return Collection<int, ShopCustomer>
     */
    public function search(int $shopId, string $term, int $limit = 8): Collection
    {
        $like = '%'.addcslashes($term, '%_\\').'%';
        // Numbers are saved as +63-917-123-4567: compare digits only, without a leading 0 or 63.
        $digits = ltrim((string) preg_replace('/\D+/', '', $term), '0');

        return ShopCustomer::query()
            ->forShop($shopId)
            ->where(function ($q) use ($like, $digits) {
                $q->where('name', 'like', $like)
                    ->orWhere('phone', 'like', $like)
                    ->orWhere('email', 'like', $like);

                if (strlen($digits) >= 3) {
                    $q->orWhereRaw("REPLACE(phone, '-', '') like ?", ['%'.$digits.'%']);
                }
            })
            ->withCount(['orders as orders_count' => fn ($q) => $q->where('status', '!=', 'cancelled')])
            ->orderByDesc('orders_count')
            ->orderBy('name')
            ->limit($limit)
            ->get();
    }

    /** The customer list with each customer's order count, amount spent and last order. */
    public function paginateForShop(int $shopId, array $filters, int $perPage): LengthAwarePaginator
    {
        $query = ShopCustomer::query()
            ->forShop($shopId)
            ->withCount(['orders as orders_count' => fn ($q) => $q->where('status', '!=', 'cancelled')])
            ->withSum(['orders as total_spent' => fn ($q) => $q->where('status', '!=', 'cancelled')], 'total_amount')
            ->withMax('orders as last_order_at', 'created_at');

        if (filled($filters['search'] ?? null)) {
            $like = '%'.addcslashes((string) $filters['search'], '%_\\').'%';
            $query->where(fn ($q) => $q->where('name', 'like', $like)
                ->orWhere('phone', 'like', $like)
                ->orWhere('email', 'like', $like));
        }

        if (($filters['segment'] ?? null) === 'returning') {
            $query->has('orders', '>=', 2);
        }

        $direction = ($filters['sort_direction'] ?? 'desc') === 'asc' ? 'asc' : 'desc';
        $column = [
            'name' => 'name',
            'orders' => 'orders_count',
            'spent' => 'total_spent',
            'last_order' => 'last_order_at',
            'created_at' => 'created_at',
        ][$filters['sort_by'] ?? 'last_order'] ?? 'last_order_at';

        return $query->orderBy($column, $direction)->orderByDesc('id')->paginate($perPage)->withQueryString();
    }

    /** @return array{total: int, returning: int, new_this_month: int} */
    public function summaryForShop(int $shopId): array
    {
        $base = ShopCustomer::query()->forShop($shopId);

        return [
            'total' => (clone $base)->count(),
            'returning' => (clone $base)->has('orders', '>=', 2)->count(),
            'new_this_month' => (clone $base)->where('created_at', '>=', now()->startOfMonth())->count(),
        ];
    }

    /** One customer's orders, newest first. */
    public function ordersFor(ShopCustomer $customer, int $perPage): LengthAwarePaginator
    {
        return ShopOrder::query()
            ->where('shop_customer_id', $customer->id)
            ->where('shop_id', $customer->shop_id)
            ->with('service')
            ->latest()
            ->paginate($perPage)
            ->withQueryString();
    }

    /** @return array{orders: int, spent: float, unpaid: float, last_order_at: string|null} */
    public function statsFor(ShopCustomer $customer): array
    {
        $orders = ShopOrder::query()
            ->where('shop_customer_id', $customer->id)
            ->where('status', '!=', 'cancelled');

        return [
            'orders' => (int) (clone $orders)->count(),
            'spent' => (float) (clone $orders)->sum('total_amount'),
            'unpaid' => (float) (clone $orders)->sum('total_amount') - (float) (clone $orders)->sum('amount_paid'),
            'last_order_at' => (clone $orders)->max('created_at'),
        ];
    }
}
