<?php

namespace App\Repositories;

use App\DTOs\TableSort;
use App\Models\Promotion;
use App\Repositories\Concerns\AppliesTableSort;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

class PromotionRepository
{
    use AppliesTableSort;

    public function paginate(int $shopId, int $perPage = 15, ?TableSort $sort = null): LengthAwarePaginator
    {
        $query = Promotion::where('shop_id', $shopId)->withCount('orders');

        $sorted = $this->applyTableSort($query, $sort, [
            'name' => 'name',
            'type' => 'type',
            'value' => 'value',
            'min_order_amount' => 'min_order_amount',
            'validity' => 'starts_at',
            'orders_count' => 'orders_count',
            'status' => 'is_active',
        ]);

        if (! $sorted) {
            $query->orderByDesc('created_at');
        }

        return $query->paginate($perPage);
    }

    public function allActive(int $shopId): Collection
    {
        return Promotion::where('shop_id', $shopId)
            ->active()
            ->valid()
            ->orderBy('name')
            ->get();
    }

    /**
     * Counts for the promotions page. A promotion is only "active" while it is switched on and
     * inside its date range, so expired and not-yet-started ones count as inactive.
     *
     * @return array{total: int, active: int, inactive: int, percentage: int, fixed: int}
     */
    public function stats(int $shopId): array
    {
        $all = Promotion::where('shop_id', $shopId);
        $total = (clone $all)->count();
        $active = (clone $all)->active()->valid()->count();

        return [
            'total' => $total,
            'active' => $active,
            'inactive' => $total - $active,
            'percentage' => (clone $all)->where('type', 'percentage')->count(),
            'fixed' => (clone $all)->where('type', 'fixed')->count(),
        ];
    }

    public function archived(int $shopId): Collection
    {
        return Promotion::onlyTrashed()
            ->where('shop_id', $shopId)
            ->orderByDesc('deleted_at')
            ->get();
    }

    public function find(int $id): Promotion
    {
        return Promotion::findOrFail($id);
    }

    public function create(array $data): Promotion
    {
        return Promotion::create($data);
    }

    public function update(Promotion $promotion, array $data): Promotion
    {
        $promotion->update($data);

        return $promotion->fresh();
    }

    public function delete(Promotion $promotion): void
    {
        $promotion->delete();
    }

    public function restore(Promotion $promotion): void
    {
        $promotion->restore();
    }
}
