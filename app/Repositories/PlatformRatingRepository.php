<?php

namespace App\Repositories;

use App\Models\OrderRating;
use App\Models\PlatformRating;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class PlatformRatingRepository extends Repository
{
    private const SORTABLE = [
        'shop' => 'shops.shop_name',
        'owner' => 'users.name',
        'rating' => 'platform_ratings.rating',
        'created_at' => 'platform_ratings.created_at',
    ];

    public function __construct(PlatformRating $model)
    {
        parent::__construct($model);
    }

    public function createFor(int $shopId, int $userId, int $rating, ?string $comment): PlatformRating
    {
        return PlatformRating::query()->create([
            'shop_id' => $shopId,
            'user_id' => $userId,
            'rating' => $rating,
            'comment' => $comment,
        ]);
    }

    /** @return array{average: float|null, count: int, this_month: int, distribution: array<int, int>} */
    public function stats(): array
    {
        $totals = PlatformRating::query()
            ->selectRaw('rating, COUNT(*) as total')
            ->groupBy('rating')
            ->pluck('total', 'rating');

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
            'this_month' => PlatformRating::query()->where('created_at', '>=', now()->startOfMonth())->count(),
            'distribution' => $distribution,
        ];
    }

    /** Every platform rating with its shop and owner, filtered and sorted in SQL. */
    public function paginateForAdmin(array $filters, int $perPage): LengthAwarePaginator
    {
        $column = self::SORTABLE[$filters['sort_by'] ?? 'created_at'] ?? self::SORTABLE['created_at'];
        $direction = ($filters['sort_direction'] ?? 'desc') === 'asc' ? 'asc' : 'desc';

        return PlatformRating::query()
            ->join('shops', 'shops.id', '=', 'platform_ratings.shop_id')
            ->join('users', 'users.id', '=', 'platform_ratings.user_id')
            ->select('platform_ratings.*', 'shops.shop_name', 'users.name as owner_name', 'users.email as owner_email')
            ->when($filters['stars'] ?? null, fn ($query, $stars) => $query->where('platform_ratings.rating', (int) $stars))
            ->when(filled($filters['search'] ?? null), function ($query) use ($filters) {
                $like = '%'.addcslashes((string) $filters['search'], '%_\\').'%';

                $query->where(fn ($inner) => $inner
                    ->where('shops.shop_name', 'like', $like)
                    ->orWhere('users.name', 'like', $like)
                    ->orWhere('users.email', 'like', $like)
                    ->orWhere('platform_ratings.comment', 'like', $like));
            })
            ->orderBy($column, $direction)
            ->orderByDesc('platform_ratings.id')
            ->paginate($perPage)
            ->withQueryString();
    }
}
