<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * Road distance between two points using the OSRM routing API, falling back to a
 * straight-line (Haversine) distance when OSRM is unreachable or returns no route.
 */
class DistanceService
{
    private const EARTH_RADIUS_KM = 6371.0;

    /**
     * @return array{distance_km: float, source: 'osrm'|'haversine'}
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
                Cache::put($key, $road, (int) config('services.osrm.cache_ttl', 86400));
            }
        }

        if ($road !== null) {
            return ['distance_km' => round((float) $road, 2), 'source' => 'osrm'];
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
        $url = sprintf(
            '%s/route/v1/driving/%F,%F;%F,%F',
            rtrim((string) config('services.osrm.url'), '/'),
            $fromLng,
            $fromLat,
            $toLng,
            $toLat,
        );

        try {
            $response = Http::timeout((int) config('services.osrm.timeout', 4))
                ->acceptJson()
                ->get($url, ['overview' => 'false']);

            $meters = $response->successful() && $response->json('code') === 'Ok'
                ? $response->json('routes.0.distance')
                : null;

            if (! is_numeric($meters)) {
                Log::warning('OSRM returned no usable route; using straight-line distance.', [
                    'status' => $response->status(),
                    'code' => $response->json('code'),
                ]);

                return null;
            }

            return (float) $meters / 1000;
        } catch (Throwable $exception) {
            Log::warning('OSRM request failed; using straight-line distance.', [
                'error' => $exception->getMessage(),
            ]);

            return null;
        }
    }
}
