<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * Road distance between two points using Mapbox Directions, falling back to a
 * straight-line (Haversine) distance when Mapbox is unreachable or returns no route.
 */
class DistanceService
{
    private const EARTH_RADIUS_KM = 6371.0;

    public function __construct(private readonly MapboxService $mapbox) {}

    /**
     * @return array{distance_km: float, source: 'mapbox'|'haversine'}
     */
    public function between(float $fromLat, float $fromLng, float $toLat, float $toLng): array
    {
        // ~1 m precision is plenty for pricing and keeps the cache hit rate high.
        $key = sprintf(
            'delivery-distance:%.5f,%.5f:%.5f,%.5f',
            $fromLat,
            $fromLng,
            $toLat,
            $toLng,
        );

        $road = Cache::get($key);

        if ($road === null) {
            $road = $this->roadDistanceKm($fromLat, $fromLng, $toLat, $toLng);

            if ($road !== null) {
                Cache::put($key, $road, (int) config('services.mapbox.route_ttl', 3600));
            }
        }

        if ($road !== null) {
            return ['distance_km' => round((float) $road, 2), 'source' => 'mapbox'];
        }

        return [
            'distance_km' => round($this->haversineKm($fromLat, $fromLng, $toLat, $toLng), 2),
            'source' => 'haversine',
        ];
    }

    public function haversineKm(float $fromLat, float $fromLng, float $toLat, float $toLng): float
    {
        $dLat = deg2rad($toLat - $fromLat);
        $dLng = deg2rad($toLng - $fromLng);

        $a = sin($dLat / 2) ** 2
            + cos(deg2rad($fromLat)) * cos(deg2rad($toLat)) * sin($dLng / 2) ** 2;

        return self::EARTH_RADIUS_KM * 2 * atan2(sqrt($a), sqrt(1 - $a));
    }

    private function roadDistanceKm(float $fromLat, float $fromLng, float $toLat, float $toLng): ?float
    {
        try {
            $route = $this->mapbox->route('pricing', $fromLat, $fromLng, $toLat, $toLng);

            return $route['distance_m'] / 1000;
        } catch (Throwable $exception) {
            Log::warning('Mapbox routing unavailable; using straight-line distance.', [
                'error' => class_basename($exception),
            ]);

            return null;
        }
    }
}
