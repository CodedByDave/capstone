<?php

use App\Enums\AccountType;
use App\Models\Delivery;
use App\Models\Shop;
use App\Models\User;
use App\Services\LogisticsService;

function mapBoardShop(array $overrides = []): Shop
{
    $owner = User::factory()->create(['role' => AccountType::ShopOwner->value]);

    return Shop::create(array_merge([
        'owner_id' => $owner->id,
        'shop_name' => 'Board Laundry',
        'phone' => '09171234567',
        'municipality' => 'Imus',
        'barangay' => 'Poblacion',
        'postal_code' => '4103',
        'status' => 'active',
        'offers_delivery' => true,
        'delivery_pricing_set_at' => now(),
        'setup_completed_at' => now(),
        'latitude' => 14.4296,
        'longitude' => 120.9367,
    ], $overrides));
}

function mapBoardDelivery(Shop $shop, array $overrides = []): Delivery
{
    return Delivery::create(array_merge([
        'shop_id' => $shop->id,
        'customer_name' => 'Ana',
        'delivery_address' => 'Blk 1, Imus',
        'status' => 'pending',
        'destination_lat' => 14.44,
        'destination_lng' => 120.95,
    ], $overrides));
}

test('the dispatch board hands the map the shop position and only its own open stops', function () {
    $shop = mapBoardShop();
    $other = mapBoardShop();

    $open = mapBoardDelivery($shop);
    mapBoardDelivery($shop, ['status' => 'delivered']);
    mapBoardDelivery($shop, ['status' => 'failed']);
    mapBoardDelivery($shop, ['destination_lat' => null, 'destination_lng' => null]);
    mapBoardDelivery($other, ['customer_name' => 'Someone else']);

    $board = app(LogisticsService::class)->board($shop->owner, null);

    expect($board['map']['shop'])->toMatchArray([
        'public_id' => $shop->public_id,
        'name' => 'Board Laundry',
        'lat' => 14.4296,
        'lng' => 120.9367,
    ])->and($board['map']['stops'])->toHaveCount(1)
        ->and($board['map']['stops'][0]['public_id'])->toBe($open->public_id)
        ->and($board['map']['stops'][0]['lat'])->toBe(14.44);
});

test('the dispatch map stops are capped', function () {
    $shop = mapBoardShop();

    foreach (range(1, 35) as $i) {
        mapBoardDelivery($shop, ['customer_name' => "Customer {$i}"]);
    }

    $board = app(LogisticsService::class)->board($shop->owner, null);

    expect($board['map']['stops'])->toHaveCount(30);
});
