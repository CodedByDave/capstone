<?php

namespace App\Services;

use App\Exceptions\MapboxUnavailableException;
use App\Exceptions\NoRouteFoundException;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * Server-side gateway to Mapbox (Geocoding v6, Directions v5, Optimization v1). The token never
 * leaves the server and every result is cached; cache keys carry a scope (the shop id) so one
 * shop's lookups never serve another's.
 */
class MapboxService
{
    public const MAX_OPTIMIZE_STOPS = 11;

    public function isConfigured(): bool
    {
        return filled(config('services.mapbox.token'));
    }

    /**
     * @return list<array{label: string, name: string, lat: float, lng: float}>
     */
    public function geocode(string $query, ?float $nearLat = null, ?float $nearLng = null): array
    {
        $query = trim($query);
        $proximity = $nearLat !== null && $nearLng !== null
            ? sprintf('%.2f,%.2f', $nearLng, $nearLat)
            : 'none';

        return Cache::remember(
            'mapbox:geocode:'.sha1(mb_strtolower($query).'|'.$proximity),
            (int) config('services.mapbox.geocode_ttl', 86400),
            function () use ($query, $nearLat, $nearLng): array {
                $params = ['q' => $query, 'country' => 'ph', 'limit' => 5, 'language' => 'en'];

                if ($nearLat !== null && $nearLng !== null) {
                    $params['proximity'] = $nearLng.','.$nearLat;
                }

                $features = $this->get('/search/geocode/v6/forward', $params)['features'] ?? [];

                return collect($features)
                    ->map(fn (array $feature): array => [
                        'label' => (string) ($feature['properties']['full_address']
                            ?? $feature['properties']['place_formatted']
                            ?? $feature['properties']['name'] ?? ''),
                        'name' => (string) ($feature['properties']['name'] ?? ''),
                        'lat' => (float) ($feature['geometry']['coordinates'][1] ?? 0),
                        'lng' => (float) ($feature['geometry']['coordinates'][0] ?? 0),
                    ])
                    ->filter(fn (array $place): bool => $place['label'] !== '')
                    ->values()
                    ->all();
            },
        );
    }

    /**
     * The address at a pinned location, split into the parts forms need, or null when Mapbox knows
     * nothing there.
     *
     * @return array{address: string, place: ?string, locality: ?string, neighborhood: ?string, street: ?string}|null
     */
    public function reverseGeocode(float $lat, float $lng): ?array
    {
        return Cache::remember(
            sprintf('mapbox:reverse:%.5f,%.5f', $lat, $lng),
            (int) config('services.mapbox.geocode_ttl', 86400),
            function () use ($lat, $lng): ?array {
                $feature = $this->get('/search/geocode/v6/reverse', [
                    'longitude' => $lng,
                    'latitude' => $lat,
                    'country' => 'ph',
                    'limit' => 1,
                    'language' => 'en',
                ])['features'][0] ?? null;

                $properties = $feature['properties'] ?? [];
                $address = $properties['full_address'] ?? $properties['place_formatted'] ?? null;

                if (blank($address)) {
                    return null;
                }

                $context = $properties['context'] ?? [];

                return [
                    'address' => (string) $address,
                    'place' => $context['place']['name'] ?? null,
                    'locality' => $context['locality']['name'] ?? null,
                    'neighborhood' => $context['neighborhood']['name'] ?? null,
                    'street' => $context['street']['name'] ?? null,
                ];
            },
        );
    }

    /**
     * Driving route between two points.
     *
     * @return array{distance_m: int, duration_s: int, geometry: array<string, mixed>, steps: list<array{instruction: string, distance_m: int, duration_s: int}>}
     *
     * @throws NoRouteFoundException
     */
    public function route(string $scope, float $fromLat, float $fromLng, float $toLat, float $toLng): array
    {
        $key = sprintf(
            'mapbox:route:%s:%.5f,%.5f:%.5f,%.5f',
            $scope,
            $fromLat,
            $fromLng,
            $toLat,
            $toLng,
        );

        $cached = Cache::get($key);

        if (is_array($cached)) {
            return $cached;
        }

        $data = $this->get(
            sprintf('/directions/v5/mapbox/driving/%F,%F;%F,%F', $fromLng, $fromLat, $toLng, $toLat),
            ['geometries' => 'geojson', 'overview' => 'full', 'steps' => 'true'],
        );

        $route = $data['routes'][0] ?? null;

        if ($route === null || ($data['code'] ?? 'Ok') !== 'Ok') {
            throw new NoRouteFoundException('No drivable route was found to that location.');
        }

        $result = [
            'distance_m' => (int) round($route['distance']),
            'duration_s' => (int) round($route['duration']),
            'geometry' => $route['geometry'],
            'steps' => collect($route['legs'][0]['steps'] ?? [])
                ->map(fn (array $step): array => [
                    'instruction' => (string) ($step['maneuver']['instruction'] ?? ''),
                    'distance_m' => (int) round($step['distance'] ?? 0),
                    'duration_s' => (int) round($step['duration'] ?? 0),
                ])
                ->all(),
        ];

        Cache::put($key, $result, (int) config('services.mapbox.route_ttl', 3600));

        return $result;
    }

    /**
     * Best visiting order for stops, starting from the origin (the shop).
     *
     * @param  list<array{lat: float, lng: float}>  $stops
     * @return array{distance_m: int, duration_s: int, geometry: array<string, mixed>, order: list<int>}
     *
     * @throws NoRouteFoundException
     */
    public function optimize(string $scope, float $originLat, float $originLng, array $stops): array
    {
        $points = [['lat' => $originLat, 'lng' => $originLng], ...array_values($stops)];
        $coordinates = collect($points)
            ->map(fn (array $p): string => sprintf('%F,%F', $p['lng'], $p['lat']))
            ->implode(';');

        $key = 'mapbox:optimize:'.$scope.':'.sha1($coordinates);

        $cached = Cache::get($key);

        if (is_array($cached)) {
            return $cached;
        }

        $data = $this->get('/optimized-trips/v1/mapbox/driving/'.$coordinates, [
            'source' => 'first',
            'destination' => 'any',
            'roundtrip' => 'false',
            'geometries' => 'geojson',
            'overview' => 'full',
        ]);

        $trip = $data['trips'][0] ?? null;

        if ($trip === null || ($data['code'] ?? 'Ok') !== 'Ok') {
            throw new NoRouteFoundException('No drivable route connects those stops.');
        }

        // waypoints[i] describes input point i; waypoint_index is its position in the optimised trip.
        $order = collect($data['waypoints'])
            ->map(fn (array $waypoint, int $input): array => ['input' => $input, 'position' => $waypoint['waypoint_index']])
            ->filter(fn (array $item): bool => $item['input'] > 0)
            ->sortBy('position')
            ->map(fn (array $item): int => $item['input'] - 1)
            ->values()
            ->all();

        $result = [
            'distance_m' => (int) round($trip['distance']),
            'duration_s' => (int) round($trip['duration']),
            'geometry' => $trip['geometry'],
            'order' => $order,
        ];

        Cache::put($key, $result, (int) config('services.mapbox.route_ttl', 3600));

        return $result;
    }

    /**
     * @param  array<string, scalar>  $params
     * @return array<string, mixed>
     */
    private function get(string $path, array $params): array
    {
        if (! $this->isConfigured()) {
            throw new MapboxUnavailableException('Maps are not configured.');
        }

        try {
            $response = $this->client()->get($path, $params + ['access_token' => config('services.mapbox.token')]);
        } catch (Throwable $exception) {
            Log::warning('Mapbox request failed.', ['path' => $path, 'error' => class_basename($exception)]);

            throw new MapboxUnavailableException('The map service could not be reached.', 0, $exception);
        }

        // 404/422 from Directions and Optimization mean "no route"; anything else is an outage.
        if (in_array($response->status(), [404, 422], true)) {
            throw new NoRouteFoundException('No drivable route was found to that location.');
        }

        if (! $response->successful()) {
            Log::warning('Mapbox returned an error.', ['path' => $path, 'status' => $response->status()]);

            throw new MapboxUnavailableException('The map service is unavailable right now.');
        }

        return $response->json() ?? [];
    }

    private function client(): PendingRequest
    {
        return Http::baseUrl(rtrim((string) config('services.mapbox.base_url'), '/'))
            ->timeout((int) config('services.mapbox.timeout', 6))
            ->acceptJson();
    }
}
