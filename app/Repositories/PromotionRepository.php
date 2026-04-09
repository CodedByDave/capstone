<?php

namespace App\Repositories;

use App\Models\Promotion;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

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
