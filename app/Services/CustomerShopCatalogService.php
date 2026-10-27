<?php

namespace App\Services;

use App\Models\Shop;
use Illuminate\Support\Collection;

/**
 * Customer-facing summaries of what a shop offers: its headline price and how each payment method works.
 */
class CustomerShopCatalogService
{
    public function __construct(private readonly ServiceCategoryService $categories) {}

    /**
     * Services as the customer sees them: the supplies each one uses (name, quantity, unit only, so
     * no costs or stock levels leave the shop) and the display name of a shop-made category.
     *
     * @param  Collection<int, \App\Models\ShopService>  $services  Active services of one shop, with supplies loaded.
     * @return list<array<string, mixed>>
     */
    public function serviceCards(int $shopId, Collection $services): array
    {
        $custom = collect($this->categories->customFor($shopId))->pluck('label', 'value');

        return $services->map(fn ($service) => [
            ...collect($service->toArray())->except('supplies')->all(),
            'category_label' => $custom[$service->category] ?? null,
            'supplies' => $service->supplies->map(fn ($item) => [
                'name' => $item->name,
                'quantity' => (float) $item->pivot->quantity,
                'unit' => $item->pivot->unit,
                'per' => match ($service->pricing_model) {
                    'per_bundle' => 'bundle',
                    'per_piece' => 'piece',
                    default => 'kg',
                },
            ])->values()->all(),
        ])->values()->all();
    }

    /**
     * Headline price for a shop card. Per-kg pricing wins when the shop has it,
     * otherwise the cheapest bundle price is used, so bundle-only shops still show a price.
     *
     * @param  Collection<int, \App\Models\ShopService>  $services  Active services of one shop.
     * @return array{amount: float|null, unit: string|null}
     */
    public function startingPrice(Collection $services): array
    {
        $perKg = $services->where('pricing_model', 'per_kg')->whereNotNull('price_per_kg')->min('price_per_kg');

        if ($perKg !== null) {
            return ['amount' => (float) $perKg, 'unit' => 'kg'];
        }

        $bundle = $services->where('pricing_model', 'per_bundle')->whereNotNull('bundle_price')->min('bundle_price');

        if ($bundle !== null) {
            return ['amount' => (float) $bundle, 'unit' => 'bundle'];
        }

        $piece = $services->where('pricing_model', 'per_piece')->whereNotNull('price_per_piece')->min('price_per_piece');

        return $piece !== null
            ? ['amount' => (float) $piece, 'unit' => 'piece']
            : ['amount' => null, 'unit' => null];
    }

    /**
     * How a customer can pay this shop for each method:
     * "online" (PayMongo checkout), "unavailable" (not set up), or "at_shop".
     *
     * @return array{cash: string, gcash: string, maya: string}
     */
    public function paymentOptions(Shop $shop): array
    {
        return [
            'cash' => 'at_shop',
            'gcash' => $this->walletMode($shop),
            'maya' => $this->walletMode($shop),
        ];
    }

    private function walletMode(Shop $shop): string
    {
        return match (true) {
            $shop->hasPaymongo() => 'online',
            default => 'unavailable',
        };
    }
}
