<?php

namespace App\Services;

use App\Models\Promotion;
use App\Models\Shop;
use App\Repositories\PromotionRepository;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

class PromotionService
{
    public function __construct(
        private readonly PromotionRepository $repo,
        private readonly ActivityLogService $activityLogService,
    ) {}

    public function paginate(Shop $shop, int $perPage = 15): LengthAwarePaginator
    {
        return $this->repo->paginate($shop->id, $perPage);
    }

    public function activeForShop(Shop $shop): Collection
    {
        return $this->repo->allActive($shop->id);
    }

    public function archived(Shop $shop): Collection
    {
        return $this->repo->archived($shop->id);
    }

    public function create(Shop $shop, array $data): Promotion
    {
        $promotion = $this->repo->create([
            'shop_id'          => $shop->id,
            'name'             => $data['name'],
            'description'      => $data['description'] ?? null,
            'type'             => $data['type'],
            'value'            => $data['value'],
            'min_order_amount' => $data['min_order_amount'] ?? null,
            'is_active'        => $data['is_active'] ?? true,
            'starts_at'        => $data['starts_at'] ?? null,
            'ends_at'          => $data['ends_at'] ?? null,
        ]);

        $this->activityLogService->log(
            subject: $promotion,
            action: 'created',
            shopId: $shop->id,
            module: 'Operations',
        );

        return $promotion;
    }

    public function update(Promotion $promotion, array $data): Promotion
    {
        $updated = $this->repo->update($promotion, [
            'name'             => $data['name'],
            'description'      => $data['description'] ?? null,
            'type'             => $data['type'],
            'value'            => $data['value'],
            'min_order_amount' => $data['min_order_amount'] ?? null,
            'is_active'        => $data['is_active'] ?? $promotion->is_active,
            'starts_at'        => $data['starts_at'] ?? null,
            'ends_at'          => $data['ends_at'] ?? null,
        ]);

        $this->activityLogService->log(
            subject: $updated,
            action: 'updated',
            shopId: $promotion->shop_id,
            module: 'Operations',
        );

        return $updated;
    }

    public function toggleActive(Promotion $promotion): Promotion
    {
        $this->repo->update($promotion, ['is_active' => !$promotion->is_active]);
        return $promotion->fresh();
    }

    public function delete(Promotion $promotion): void
    {
        $this->activityLogService->log(
            subject: $promotion,
            action: 'archived',
            shopId: $promotion->shop_id,
            module: 'Operations',
        );

        $this->repo->delete($promotion);
    }

    public function restore(Promotion $promotion): void
    {
        $this->repo->restore($promotion);

        $this->activityLogService->log(
            subject: $promotion,
            action: 'restored',
            shopId: $promotion->shop_id,
            module: 'Operations',
        );
    }

    public function getStats(Shop $shop): array
    {
        $all = Promotion::where('shop_id', $shop->id);

        return [
            'total'      => (clone $all)->count(),
            'active'     => (clone $all)->where('is_active', true)->count(),
            'inactive'   => (clone $all)->where('is_active', false)->count(),
            'percentage' => (clone $all)->where('type', 'percentage')->count(),
            'fixed'      => (clone $all)->where('type', 'fixed')->count(),
        ];
    }
}
