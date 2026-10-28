<?php

namespace App\Repositories;

use App\Models\Promotion;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

class PromotionRepository
{
    public function paginate(int $shopId, int $perPage = 15): LengthAwarePaginator
    {
        return Promotion::where('shop_id', $shopId)
            ->withCount('orders')
            ->orderByDesc('created_at')
            ->paginate($perPage);
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
