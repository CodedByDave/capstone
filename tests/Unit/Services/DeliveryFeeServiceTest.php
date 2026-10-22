<?php

use App\Exceptions\DeliveryNotAvailableException;
use App\Models\Shop;
use App\Services\DeliveryFeeService;
use Tests\TestCase;

uses(TestCase::class);

function feeShop(array $attributes = []): Shop
{
    return new Shop(array_merge([
        'shop_name' => 'Fee Laundry',
        'offers_delivery' => true,
        'delivery_pricing_set_at' => now(),
        'latitude' => 14.4296,
        'longitude' => 120.9367,
        'delivery_fee_mode' => 'fixed',
        'delivery_fixed_fee' => 50,
        'delivery_base_fee' => 0,
        'delivery_per_km_rate' => 0,
        'delivery_free_km' => 0,
        'delivery_min_fee' => 0,
        'delivery_max_km' => null,
    ], $attributes));
}

it('charges the fixed fee regardless of distance', function () {
    $service = new DeliveryFeeService;

    expect($service->calculate(feeShop(), 0.4))->toBe(50.0)
        ->and($service->calculate(feeShop(), 18.2))->toBe(50.0);
});

it('charges base fee plus rate per kilometer', function () {
    $shop = feeShop([
        'delivery_fee_mode' => 'per_km',
        'delivery_base_fee' => 30,
        'delivery_per_km_rate' => 12.5,
    ]);

    // 30 + 4.2 * 12.5 = 82.5
    expect((new DeliveryFeeService)->calculate($shop, 4.2))->toBe(82.5);
});

it('does not charge the per kilometer rate within the free kilometers', function () {
    $shop = feeShop([
        'delivery_fee_mode' => 'per_km',
        'delivery_base_fee' => 20,
        'delivery_per_km_rate' => 10,
        'delivery_free_km' => 2,
    ]);
    $service = new DeliveryFeeService;

    expect($service->calculate($shop, 1.5))->toBe(20.0)
        // 20 + (5 - 2) * 10
        ->and($service->calculate($shop, 5))->toBe(50.0);
});

it('never charges below the minimum fee', function () {
    $shop = feeShop([
        'delivery_fee_mode' => 'per_km',
        'delivery_base_fee' => 10,
        'delivery_per_km_rate' => 5,
        'delivery_min_fee' => 40,
    ]);
    $service = new DeliveryFeeService;

    expect($service->calculate($shop, 1))->toBe(40.0)
        // 10 + 10 * 5 = 60 is above the minimum
        ->and($service->calculate($shop, 10))->toBe(60.0);
});

it('rounds the fee to two decimals', function () {
    $shop = feeShop([
        'delivery_fee_mode' => 'per_km',
        'delivery_base_fee' => 0,
        'delivery_per_km_rate' => 13.33,
    ]);

    expect((new DeliveryFeeService)->calculate($shop, 1.237))->toBe(16.49);
});

it('rejects destinations beyond the delivery radius', function () {
    $shop = feeShop(['delivery_max_km' => 5]);
    $service = new DeliveryFeeService;

    expect($service->calculate($shop, 5))->toBe(50.0);
    expect(fn () => $service->calculate($shop, 5.01))
        ->toThrow(DeliveryNotAvailableException::class, 'delivers within 5 km');
});

it('rejects quotes when the shop has no coordinates', function () {
    $shop = feeShop(['latitude' => null, 'longitude' => null]);

    expect(fn () => (new DeliveryFeeService)->calculate($shop, 1))
        ->toThrow(DeliveryNotAvailableException::class, 'has not set its location');
});

it('rejects quotes when the shop does not offer delivery', function () {
    expect(fn () => (new DeliveryFeeService)->calculate(feeShop(['offers_delivery' => false]), 1))
        ->toThrow(DeliveryNotAvailableException::class);
});
