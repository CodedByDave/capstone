<?php

namespace App\Services;

use App\Exceptions\DeliveryNotAvailableException;
use App\Models\Shop;

/**
 * Turns a shop's delivery pricing settings and a distance into a delivery fee.
 */
class DeliveryFeeService
{
    public function calculate(Shop $shop, float $distanceKm): float
    {
        $this->assertDeliverable($shop, $distanceKm);

        if ($shop->delivery_fee_mode === Shop::DELIVERY_FEE_PER_KM) {
            $chargeableKm = max(0, $distanceKm - (float) $shop->delivery_free_km);
            $fee = (float) $shop->delivery_base_fee + $chargeableKm * (float) $shop->delivery_per_km_rate;

            return round(max((float) $shop->delivery_min_fee, $fee), 2);
        }

        return round((float) $shop->delivery_fixed_fee, 2);
    }

    public function assertShopCanQuote(Shop $shop): void
    {
        if (! $shop->offers_delivery) {
            throw new DeliveryNotAvailableException('This shop does not offer delivery.');
        }

        if (! $shop->hasCoordinates()) {
            throw new DeliveryNotAvailableException(
                'Delivery quotes are unavailable because the shop has not set its location yet.',
            );
        }
    }

    private function assertDeliverable(Shop $shop, float $distanceKm): void
    {
        $this->assertShopCanQuote($shop);

        $maxKm = $shop->delivery_max_km;

        if ($maxKm !== null && $distanceKm > (float) $maxKm) {
            throw new DeliveryNotAvailableException(sprintf(
                'This address is %s km away. %s delivers within %s km only.',
                number_format($distanceKm, 1),
                $shop->shop_name ?: 'The shop',
                rtrim(rtrim(number_format((float) $maxKm, 2), '0'), '.'),
            ));
        }
    }
}
