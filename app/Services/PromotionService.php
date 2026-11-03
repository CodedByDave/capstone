<?php

namespace App\Services;

use App\DTOs\TableSort;
use App\Models\Promotion;
use App\Models\Shop;
use App\Repositories\PromotionRepository;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Validation\ValidationException;

class PromotionService
{
    public function __construct(
        private readonly PromotionRepository $repo,
        private readonly ActivityLogService $activityLogService,
    ) {}

    public function paginate(Shop $shop, int $perPage = 15, ?TableSort $sort = null): LengthAwarePaginator
    {
        return $this->repo->paginate($shop->id, $perPage, $sort);
    }

    public function activeForShop(Shop $shop): Collection
    {
        return $this->repo->allActive($shop->id);
    }

    /**
     * Promotions a customer can currently use at a shop, shaped for display.
     *
     * @return list<array{id: int, name: string, headline: string, description: ?string, min_order_amount: ?float, ends_at: ?string}>
     */
    public function forCustomer(int $shopId): array
    {
        return $this->repo->allActive($shopId)
            ->map(fn (Promotion $promotion) => [
                'id' => $promotion->id,
                'type' => $promotion->type,
                'value' => (float) $promotion->value,
                'name' => $promotion->name,
                'headline' => $promotion->type === 'percentage'
                    ? rtrim(rtrim(number_format((float) $promotion->value, 2, '.', ''), '0'), '.').'% off'
                    : '₱'.number_format((float) $promotion->value, 0).' off',
                'description' => $promotion->description,
                'min_order_amount' => $promotion->min_order_amount !== null ? (float) $promotion->min_order_amount : null,
                'ends_at' => $promotion->ends_at?->toDateString(),
            ])
            ->values()
            ->all();
    }

    /**
     * The discount a customer gets from a promotion on an order total. A promotion that is not the
     * shop's, is switched off, expired, or whose minimum order is not met is rejected, never ignored.
     */
    public function discountFor(int $shopId, int $promotionId, float $total): float
    {
        $promotion = $this->repo->findUsableForShop($promotionId, $shopId);

        if (! $promotion) {
            throw ValidationException::withMessages(['promotion_id' => 'This promotion is no longer available.']);
        }

        if ($promotion->min_order_amount !== null && $total < (float) $promotion->min_order_amount) {
            throw ValidationException::withMessages([
                'promotion_id' => 'This promotion needs an order of at least ₱'.number_format((float) $promotion->min_order_amount, 2).'.',
            ]);
        }

        return $promotion->computeDiscount($total);
    }

    public function archived(Shop $shop): Collection
    {
        return $this->repo->archived($shop->id);
    }

    public function create(Shop $shop, array $data): Promotion
    {
        $promotion = $this->repo->create([
            'shop_id' => $shop->id,
            'name' => $data['name'],
            'description' => $data['description'] ?? null,
            'type' => $data['type'],
            'value' => $data['value'],
            'min_order_amount' => $data['min_order_amount'] ?? null,
            'is_active' => $data['is_active'] ?? true,
            'starts_at' => $data['starts_at'] ?? null,
            'ends_at' => $data['ends_at'] ?? null,
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
            'name' => $data['name'],
            'description' => $data['description'] ?? null,
            'type' => $data['type'],
            'value' => $data['value'],
            'min_order_amount' => $data['min_order_amount'] ?? null,
            'is_active' => $data['is_active'] ?? $promotion->is_active,
            'starts_at' => $data['starts_at'] ?? null,
            'ends_at' => $data['ends_at'] ?? null,
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
        $this->repo->update($promotion, ['is_active' => ! $promotion->is_active]);

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
        return $this->repo->stats($shop->id);
    }
}
