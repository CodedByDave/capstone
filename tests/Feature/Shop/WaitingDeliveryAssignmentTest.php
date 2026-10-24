<?php

use App\Enums\AccountType;
use App\Models\Delivery;
use App\Models\Rider;
use App\Models\Shop;
use App\Models\User;
use App\Services\LogisticsService;

function waitingShop(): Shop
{
    $owner = User::factory()->create(['role' => AccountType::ShopOwner->value]);

    return Shop::create([
        'owner_id' => $owner->id,
        'shop_name' => 'Queue Laundry',
        'phone' => '09171234567',
        'municipality' => 'Imus',
        'barangay' => 'Poblacion',
        'postal_code' => '4103',
        'status' => 'active',
        'offers_delivery' => true,
        'delivery_pricing_set_at' => now(),
    ]);
}

function makeDelivery(Shop $shop, string $status, ?Rider $rider = null): Delivery
{
    return Delivery::create([
        'shop_id' => $shop->id,
        'customer_name' => 'Customer',
        'delivery_address' => 'Anabu, Imus',
        'status' => $status,
        'rider_id' => $rider?->id,
    ]);
}

test('a rider who finishes a run takes the oldest waiting delivery', function () {
    $shop = waitingShop();
    $rider = Rider::create(['shop_id' => $shop->id, 'name' => 'Rico', 'status' => 'active']);
    $onRoad = makeDelivery($shop, 'picked_up', $rider);
    $first = makeDelivery($shop, 'pending');
    $second = makeDelivery($shop, 'pending');

    $this->patchJson("/driver/{$onRoad->driver_token}/status", ['status' => 'delivered'])->assertRedirect();

    expect($onRoad->fresh()->status)->toBe('delivered')
        ->and($first->fresh()->status)->toBe('assigned')
        ->and($first->fresh()->rider_id)->toBe($rider->id)
        ->and($first->fresh()->assigned_at)->not->toBeNull()
        ->and($second->fresh()->status)->toBe('pending')
        ->and($second->fresh()->rider_id)->toBeNull();
});

test('waiting deliveries stay pending while every rider is busy', function () {
    $shop = waitingShop();
    $rider = Rider::create(['shop_id' => $shop->id, 'name' => 'Rico', 'status' => 'active']);
    makeDelivery($shop, 'assigned', $rider);
    $waiting = makeDelivery($shop, 'pending');

    expect(app(LogisticsService::class)->assignWaitingDeliveries($shop->id))->toBe(0)
        ->and($waiting->fresh()->status)->toBe('pending');
});

test('several free riders each take one waiting delivery', function () {
    $shop = waitingShop();
    Rider::create(['shop_id' => $shop->id, 'name' => 'Rico', 'status' => 'active']);
    Rider::create(['shop_id' => $shop->id, 'name' => 'Rex', 'status' => 'active']);
    $a = makeDelivery($shop, 'pending');
    $b = makeDelivery($shop, 'pending');
    $c = makeDelivery($shop, 'pending');

    expect(app(LogisticsService::class)->assignWaitingDeliveries($shop->id))->toBe(2)
        ->and($a->fresh()->rider_id)->not->toBeNull()
        ->and($b->fresh()->rider_id)->not->toBe($a->fresh()->rider_id)
        ->and($c->fresh()->status)->toBe('pending');
});

test('a rider from another shop never takes this shop\'s waiting delivery', function () {
    $shop = waitingShop();
    $other = waitingShop();
    Rider::create(['shop_id' => $other->id, 'name' => 'Rex', 'status' => 'active']);
    $waiting = makeDelivery($shop, 'pending');

    app(LogisticsService::class)->assignWaitingDeliveries($other->id);

    expect($waiting->fresh()->status)->toBe('pending');
});
