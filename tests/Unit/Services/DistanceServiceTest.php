<?php

use App\Services\DistanceService;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

uses(TestCase::class);

function mapboxRouteResponse(float $meters): array
{
    return [
        'code' => 'Ok',
        'routes' => [[
            'distance' => $meters,
            'duration' => 600.0,
            'geometry' => ['type' => 'LineString', 'coordinates' => [[120.9367, 14.4296], [120.95, 14.45]]],
            'legs' => [['steps' => []]],
        ]],
    ];
}

beforeEach(function () {
    config()->set('services.mapbox.token', 'pk.test');
    config()->set('services.mapbox.base_url', 'https://mapbox.test');
    Cache::flush();
});

it('uses the Mapbox road distance when the route is available', function () {
    Http::fake(['mapbox.test/*' => Http::response(mapboxRouteResponse(4321.0))]);

    $result = app(DistanceService::class)->between(14.4296, 120.9367, 14.4500, 120.9500);

    expect($result)->toBe(['distance_km' => 4.32, 'source' => 'mapbox']);
    Http::assertSent(fn (Request $request) => str_starts_with(
        $request->url(),
        'https://mapbox.test/directions/v5/mapbox/driving/120.936700,14.429600;120.950000,14.450000',
    ));
});

it('caches road distances for repeated lookups', function () {
    Http::fake(['mapbox.test/*' => Http::response(mapboxRouteResponse(1000.0))]);
    $service = app(DistanceService::class);

    $service->between(14.4296, 120.9367, 14.4500, 120.9500);
    $service->between(14.4296, 120.9367, 14.4500, 120.9500);

    Http::assertSentCount(1);
});

it('falls back to the straight-line distance when Mapbox fails', function () {
    Http::fake(['mapbox.test/*' => Http::response('Server error', 500)]);
    $service = app(DistanceService::class);

    $result = $service->between(14.4296, 120.9367, 14.4500, 120.9500);

    expect($result['source'])->toBe('haversine')
        ->and($result['distance_km'])->toBe(round($service->haversineKm(14.4296, 120.9367, 14.4500, 120.9500), 2))
        ->and($result['distance_km'])->toBeGreaterThan(2.5)->toBeLessThan(2.8);
});

it('falls back to the straight-line distance when Mapbox is unreachable', function () {
    Http::fake(fn () => throw new ConnectionException('timed out'));

    $result = app(DistanceService::class)->between(14.4296, 120.9367, 14.4500, 120.9500);

    expect($result['source'])->toBe('haversine');
});

it('falls back when Mapbox finds no route', function () {
    Http::fake(['mapbox.test/*' => Http::response(['code' => 'NoRoute', 'routes' => []])]);

    expect(app(DistanceService::class)->between(14.4, 120.9, 14.5, 121.0)['source'])->toBe('haversine');
});

it('falls back when no Mapbox token is configured', function () {
    config()->set('services.mapbox.token', null);
    Http::fake();

    expect(app(DistanceService::class)->between(14.4, 120.9, 14.5, 121.0)['source'])->toBe('haversine');
    Http::assertNothingSent();
});
