<?php

namespace App\Services;

use App\Models\ShopServicePricing;
use App\Repositories\ShopServiceRepository;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Validation\ValidationException;

class ShopServiceService
{
    public function __construct(
        protected ShopServiceRepository $repo
    ) {}

    public function getAll(int $shopId, bool $activeOnly = false, bool $onlyTrashed = false): Collection
    {
        return $this->repo->allByShop($shopId, $activeOnly, $onlyTrashed);
    }

    public function create(array $data): ShopServicePricing
    {
        $this->validatePricingFields($data);
        return $this->repo->create($data);
    }

    public function update(ShopServicePricing $service, array $data): ShopServicePricing
    {
        $this->validatePricingFields($data);
        return $this->repo->update($service, $data);
    }

    public function delete(ShopServicePricing $service): void
    {
        $this->repo->delete($service);
    }

    public function toggleActive(ShopServicePricing $service): ShopServicePricing
    {
        return $this->repo->update($service, [
            'is_active' => ! $service->is_active,
        ]);
    }

    public function restore(ShopServicePricing $service): void
    {
        $service->restore();
    }

    public function restoreAll(int $shopId): void
    {
        ShopServicePricing::onlyTrashed()->where('shop_id', $shopId)->restore();
    }

    // ─── Private ───────────────────────────────────────

    private function validatePricingFields(array $data): void
    {
        $model = $data['pricing_model'] ?? null;

        if ($model === 'per_kg' && empty($data['price_per_kg'])) {
            throw ValidationException::withMessages([
                'price_per_kg' => 'Price per kg is required for per-kg pricing.',
            ]);
        }

        if ($model === 'per_bundle') {
            if (empty($data['bundle_price'])) {
                throw ValidationException::withMessages([
                    'bundle_price' => 'Bundle price is required for bundle pricing.',
                ]);
            }
            if (empty($data['bundle_weight_kg'])) {
                throw ValidationException::withMessages([
                    'bundle_weight_kg' => 'Bundle weight is required for bundle pricing.',
                ]);
            }
        }
    }
}