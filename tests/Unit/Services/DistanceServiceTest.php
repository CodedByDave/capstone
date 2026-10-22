<?php

use App\Services\DistanceService;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

uses(TestCase::class);

beforeEach(function () {
    config()->set('services.osrm.url', 'https://osrm.test');
    Cache::flush();
});

it('uses the OSRM road distance when the route is available', function () {
    Http::fake([
        'osrm.test/*' => Http::response(['code' => 'Ok', 'routes' => [['distance' => 4321.0]]]),
    ]);

    $result = app(DistanceService::class)->between(14.4296, 120.9367, 14.4500, 120.9500);

    expect($result)->toBe(['distance_km' => 4.32, 'source' => 'osrm']);
    Http::assertSent(fn (Request $request) => str_starts_with(
        $request->url(),
        'https://osrm.test/route/v1/driving/120.936700,14.429600;120.950000,14.450000',
    ));
});

it('caches road distances for repeated lookups', function () {
    Http::fake([
        'osrm.test/*' => Http::response(['code' => 'Ok', 'routes' => [['distance' => 1000.0]]]),
    ]);
    $service = app(DistanceService::class);

    $service->between(14.4296, 120.9367, 14.4500, 120.9500);
    $service->between(14.4296, 120.9367, 14.4500, 120.9500);

    Http::assertSentCount(1);
});

it('falls back to the straight-line distance when OSRM fails', function () {
    Http::fake(['osrm.test/*' => Http::response('Server error', 500)]);
    $service = app(DistanceService::class);

    $result = $service->between(14.4296, 120.9367, 14.4500, 120.9500);

    expect($result['source'])->toBe('haversine')
        ->and($result['distance_km'])->toBe(round($service->haversineKm(14.4296, 120.9367, 14.4500, 120.9500), 2))
        ->and($result['distance_km'])->toBeGreaterThan(2.5)->toBeLessThan(2.8);
});

it('falls back to the straight-line distance when OSRM is unreachable', function () {
    Http::fake(fn () => throw new ConnectionException('timed out'));

    $result = app(DistanceService::class)->between(14.4296, 120.9367, 14.4500, 120.9500);

    expect($result['source'])->toBe('haversine');
});

it('falls back when OSRM finds no route', function () {
    Http::fake(['osrm.test/*' => Http::response(['code' => 'NoRoute', 'routes' => []])]);

    expect(app(DistanceService::class)->between(14.4, 120.9, 14.5, 121.0)['source'])->toBe('haversine');
});
