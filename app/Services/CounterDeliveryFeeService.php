<?php

namespace App\Services;

use App\Exceptions\DeliveryNotAvailableException;
use App\Models\Shop;
use App\Repositories\ShopRepository;

/**
 * The delivery fee for an order taken at the counter that a rider will bring back to the customer.
 * A fixed fee needs nothing more; per-kilometer pricing needs the customer's pinned location so the
 * distance from the shop can be measured. The fee always comes from the shop's own settings.
 */
class CounterDeliveryFeeService
{
    public function __construct(
        private readonly ShopRepository $shops,
        private readonly DeliveryFeeService $fees,
        private readonly DistanceService $distance,
    ) {}

    /**
     * What the order form needs to show the fee before the order is saved.
     *
     * @return array{available: bool, mode: string|null, fixed_fee: float, needs_location: bool, shop_lat: float|null, shop_lng: float|null}
     */
    public function pricingFor(int $shopId): array
    {
        $shop = $this->shops->findShopById($shopId);
        $available = $shop !== null && $shop->canDeliver();
        $perKm = $available && $shop->delivery_fee_mode === Shop::DELIVERY_FEE_PER_KM;

        return [
            'available' => $available,
            'mode' => $available ? ($shop->delivery_fee_mode ?: Shop::DELIVERY_FEE_FIXED) : null,
            'fixed_fee' => $available && ! $perKm ? round((float) $shop->delivery_fixed_fee, 2) : 0.0,
            'needs_location' => $perKm,
            'shop_lat' => $shop?->latitude !== null ? (float) $shop->latitude : null,
            'shop_lng' => $shop?->longitude !== null ? (float) $shop->longitude : null,
        ];
    }

    /**
     * The fee and how it was worked out. The distance is measured only for per-kilometer pricing.
     *
     * @return array{fee: float, distance_km: float|null, breakdown: array<string, mixed>}
     *
     * @throws DeliveryNotAvailableException when the shop cannot deliver, or the location is missing or too far
     */
    public function quote(int $shopId, ?float $lat, ?float $lng): array
    {
        $shop = $this->shops->findShopById($shopId);

        if ($shop === null || ! $shop->canDeliver()) {
            throw new DeliveryNotAvailableException('Delivery is not available until the shop sets its delivery pricing.');
        }

        if ($shop->delivery_fee_mode !== Shop::DELIVERY_FEE_PER_KM) {
            // A fixed fee does not depend on where the customer is.
            $fee = round((float) $shop->delivery_fixed_fee, 2);

            return ['fee' => $fee, 'distance_km' => null, 'breakdown' => ['mode' => Shop::DELIVERY_FEE_FIXED, 'fee' => $fee]];
        }

        if ($lat === null || $lng === null) {
            throw new DeliveryNotAvailableException('Pin the customer’s location on the map so the delivery fee can be worked out from the distance.');
        }

        // Checks pricing and the shop's own location before any distance is measured.
        $this->fees->assertShopCanQuote($shop);
        $distanceKm = $this->distance->between((float) $shop->latitude, (float) $shop->longitude, $lat, $lng)['distance_km'];
        $breakdown = $this->fees->breakdown($shop, $distanceKm);

        return ['fee' => $breakdown['fee'], 'distance_km' => $distanceKm, 'breakdown' => $breakdown];
    }
}
