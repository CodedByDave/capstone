<?php

namespace App\Services;

use App\Models\Shop;
use Throwable;

/**
 * The delivery radius a shop sets in Settings. A run to an address beyond it is never given
 * to a rider automatically: it is held on the Logistics board for staff to decide.
 */
class DeliveryRadiusService
{
    public function __construct(
        private readonly DistanceService $distances,
        private readonly MapboxService $mapbox,
    ) {}

    public function hasLimit(Shop $shop): bool
    {
        return $shop->delivery_max_km !== null && $shop->hasCoordinates();
    }

    /**
     * Why a run to this point is held, or null when it is inside the radius or the shop has none.
     * A point that cannot be located is held too, because its distance cannot be verified.
     */
    public function heldReason(Shop $shop, ?float $lat, ?float $lng): ?string
    {
        if (! $this->hasLimit($shop)) {
            return null;
        }

        if ($lat === null || $lng === null) {
            return 'Could not verify the distance to this address';
        }

        $km = $this->distances->between((float) $shop->latitude, (float) $shop->longitude, $lat, $lng)['distance_km'];
        $max = (float) $shop->delivery_max_km;

        if ($km <= $max) {
            return null;
        }

        return sprintf(
            'Outside delivery radius (%s km / max %s km)',
            number_format($km, 1),
            rtrim(rtrim(number_format($max, 2), '0'), '.'),
        );
    }

    /**
     * Finds the map point of a typed address, nearest the shop. Null when it cannot be located.
     *
     * @return array{lat: float, lng: float}|null
     */
    public function locate(Shop $shop, string $address): ?array
    {
        try {
            $first = $this->mapbox->geocode(
                $address,
                $shop->latitude !== null ? (float) $shop->latitude : null,
                $shop->longitude !== null ? (float) $shop->longitude : null,
            )[0] ?? null;
        } catch (Throwable) {
            return null;
        }

        return $first ? ['lat' => (float) $first['lat'], 'lng' => (float) $first['lng']] : null;
    }
}
