<?php

namespace App\Services;

use App\Models\Shop;
use Illuminate\Support\Collection;

/**
 * Customer-facing summaries of what a shop offers: its headline price and how each payment method works.
 */
class CustomerShopCatalogService
{
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

        return $bundle !== null
            ? ['amount' => (float) $bundle, 'unit' => 'bundle']
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
