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
        return $this->breakdown($shop, $distanceKm)['fee'];
    }

    /**
     * The fee and how it was worked out, so a customer can see where the amount comes from.
     *
     * @return array{mode: string, fee: float, base_fee: float, free_km: float, chargeable_km: float, rate_per_km: float, distance_fee: float, min_fee: float, min_applied: bool}
     */
    public function breakdown(Shop $shop, float $distanceKm): array
    {
        $this->assertDeliverable($shop, $distanceKm);

        if ($shop->delivery_fee_mode !== Shop::DELIVERY_FEE_PER_KM) {
            $fixed = round((float) $shop->delivery_fixed_fee, 2);

            return [
                'mode' => Shop::DELIVERY_FEE_FIXED,
                'fee' => $fixed,
                'base_fee' => $fixed,
                'free_km' => 0.0,
                'chargeable_km' => 0.0,
                'rate_per_km' => 0.0,
                'distance_fee' => 0.0,
                'min_fee' => 0.0,
                'min_applied' => false,
            ];
        }

        $freeKm = (float) $shop->delivery_free_km;
        $chargeableKm = round(max(0, $distanceKm - $freeKm), 1);
        $rate = (float) $shop->delivery_per_km_rate;
        $base = (float) $shop->delivery_base_fee;
        $minFee = (float) $shop->delivery_min_fee;
        $fee = $base + max(0, $distanceKm - $freeKm) * $rate;

        return [
            'mode' => Shop::DELIVERY_FEE_PER_KM,
            'fee' => round(max($minFee, $fee), 2),
            'base_fee' => round($base, 2),
            'free_km' => $freeKm,
            'chargeable_km' => $chargeableKm,
            'rate_per_km' => round($rate, 2),
            'distance_fee' => round(max(0, $distanceKm - $freeKm) * $rate, 2),
            'min_fee' => round($minFee, 2),
            'min_applied' => $minFee > $fee,
        ];
    }

    public function assertShopCanQuote(Shop $shop): void
    {
        if (! $shop->offers_delivery) {
            throw new DeliveryNotAvailableException('This shop does not offer delivery.');
        }

        if (! $shop->hasDeliveryPricing()) {
            throw new DeliveryNotAvailableException('This shop has not set its delivery pricing yet.');
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
