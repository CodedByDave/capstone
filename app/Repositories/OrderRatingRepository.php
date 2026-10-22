<?php

namespace App\Repositories;

use App\Models\OrderRating;
use App\Models\ShopOrder;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;

class OrderRatingRepository extends Repository
{
    private const SORTABLE = [
        'order_number' => 'shop_orders.order_number',
        'customer' => 'shop_orders.customer_name',
        'rating' => 'order_ratings.rating',
        'created_at' => 'order_ratings.created_at',
    ];

    public function __construct(OrderRating $model)
    {
        parent::__construct($model);
    }

    public function createFor(ShopOrder $order, int $rating, ?string $comment): OrderRating
    {
        return OrderRating::query()->create([
            'shop_order_id' => $order->id,
            'shop_id' => $order->shop_id,
            'user_id' => $order->user_id,
            'rating' => $rating,
            'comment' => $comment,
        ]);
    }

    /**
     * @param  string|null  $branch  limit to one branch's orders, like the orders list does for staff
     * @return array{average: float|null, count: int, distribution: array<int, int>}
     */
    public function summaryForShop(int $shopId, ?string $branch = null): array
    {
        $query = OrderRating::query()
            ->where('order_ratings.shop_id', $shopId)
            ->selectRaw('order_ratings.rating as rating, COUNT(*) as total')
            ->groupBy('order_ratings.rating');

        if ($branch !== null) {
            $query->join('shop_orders', 'shop_orders.id', '=', 'order_ratings.shop_order_id');
            $this->limitToBranch($query, $branch);
        }

        return $this->summarize($query->pluck('total', 'rating')->all());
    }

    /**
     * Average and count for several shops in one query.
     *
     * @param  list<int>  $shopIds
     * @return array<int, array{average: float, count: int}>
     */
    public function summariesForShops(array $shopIds): array
    {
        if ($shopIds === []) {
            return [];
        }

        return OrderRating::query()
            ->whereIn('shop_id', $shopIds)
            ->selectRaw('shop_id, AVG(rating) as average, COUNT(*) as total')
            ->groupBy('shop_id')
            ->get()
            ->mapWithKeys(fn ($row) => [
                (int) $row->shop_id => [
                    'average' => round((float) $row->average, 1),
                    'count' => (int) $row->total,
                ],
            ])
            ->all();
    }

    /** One shop's ratings with the order and customer they belong to, filtered and sorted in SQL. */
    public function paginateForShop(int $shopId, array $filters, int $perPage, ?string $branch = null): LengthAwarePaginator
    {
        $column = self::SORTABLE[$filters['sort_by'] ?? 'created_at'] ?? self::SORTABLE['created_at'];
        $direction = ($filters['sort_direction'] ?? 'desc') === 'asc' ? 'asc' : 'desc';

        $query = OrderRating::query()
            ->join('shop_orders', 'shop_orders.id', '=', 'order_ratings.shop_order_id')
            ->where('order_ratings.shop_id', $shopId)
            ->select('order_ratings.*', 'shop_orders.order_number', 'shop_orders.customer_name')
            ->when($filters['stars'] ?? null, fn ($query, $stars) => $query->where('order_ratings.rating', (int) $stars))
            ->when(filled($filters['search'] ?? null), function ($query) use ($filters) {
                $like = '%'.addcslashes((string) $filters['search'], '%_\\').'%';

                $query->where(fn ($inner) => $inner
                    ->where('shop_orders.order_number', 'like', $like)
                    ->orWhere('shop_orders.customer_name', 'like', $like)
                    ->orWhere('order_ratings.comment', 'like', $like));
            });

        if ($branch !== null) {
            $this->limitToBranch($query, $branch);
        }

        return $query
            ->orderBy($column, $direction)
            ->orderByDesc('order_ratings.id')
            ->paginate($perPage)
            ->withQueryString();
    }

    /** The same branch rule as the orders list: the branch's orders plus orders with no branch. */
    private function limitToBranch(Builder $query, string $branch): void
    {
        $query->where(fn ($inner) => $inner
            ->where('shop_orders.branch_name', $branch)
            ->orWhereNull('shop_orders.branch_name')
            ->orWhere('shop_orders.branch_name', ''));
    }

    /**
     * @param  array<int|string, int|string>  $totals  star value => how many ratings
     * @return array{average: float|null, count: int, distribution: array<int, int>}
     */
    private function summarize(array $totals): array
    {
        $distribution = [];
        $count = 0;
        $sum = 0;

        foreach (range(OrderRating::MAX, OrderRating::MIN) as $star) {
            $distribution[$star] = (int) ($totals[$star] ?? 0);
            $count += $distribution[$star];
            $sum += $star * $distribution[$star];
        }

        return [
            'average' => $count > 0 ? round($sum / $count, 1) : null,
            'count' => $count,
            'distribution' => $distribution,
        ];
    }
}
