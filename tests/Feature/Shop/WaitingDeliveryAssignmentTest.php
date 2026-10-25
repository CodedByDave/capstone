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
    $rider = onlineRider($shop, 'Rico');
    $onRoad = makeDelivery($shop, 'picked_up', $rider);
    $first = makeDelivery($shop, 'pending');
    $second = makeDelivery($shop, 'pending');

    $this->actingAs($rider->employee->user)
        ->patch("/driver/jobs/{$onRoad->public_id}/status", ['status' => 'delivered'])
        ->assertRedirect();

    expect($onRoad->fresh()->status)->toBe('delivered')
        ->and($first->fresh()->status)->toBe('assigned')
        ->and($first->fresh()->rider_id)->toBe($rider->id)
        ->and($first->fresh()->assigned_at)->not->toBeNull()
        ->and($second->fresh()->status)->toBe('pending')
        ->and($second->fresh()->rider_id)->toBeNull();
});

test('waiting deliveries stay pending while every rider is busy', function () {
    $shop = waitingShop();
    $rider = onlineRider($shop, 'Rico');
    makeDelivery($shop, 'assigned', $rider);
    $waiting = makeDelivery($shop, 'pending');

    expect(app(LogisticsService::class)->assignWaitingDeliveries($shop->id))->toBe(0)
        ->and($waiting->fresh()->status)->toBe('pending');
});

test('several free riders each take one waiting delivery', function () {
    $shop = waitingShop();
    onlineRider($shop, 'Rico');
    onlineRider($shop, 'Rex');
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
    onlineRider($other, 'Rex');
    $waiting = makeDelivery($shop, 'pending');

    app(LogisticsService::class)->assignWaitingDeliveries($other->id);

    expect($waiting->fresh()->status)->toBe('pending');
});

test('the driver who has been free the longest takes the next waiting delivery', function () {
    $shop = waitingShop();
    $recent = onlineRider($shop, 'Recent', freeSince: now()->subMinutes(5));
    $longest = onlineRider($shop, 'Longest', freeSince: now()->subMinutes(45));
    $delivery = makeDelivery($shop, 'pending');

    app(LogisticsService::class)->assignWaitingDeliveries($shop->id);

    expect($delivery->fresh()->rider_id)->toBe($longest->id)
        ->and($recent->deliveries()->count())->toBe(0);
});

test('offline drivers and riders without an employee record are never given a delivery', function () {
    $shop = waitingShop();
    onlineRider($shop, 'Offline', online: false);
    Rider::create(['shop_id' => $shop->id, 'name' => 'Legacy', 'status' => 'active', 'is_online' => true]);
    $delivery = makeDelivery($shop, 'pending');

    expect(app(LogisticsService::class)->assignWaitingDeliveries($shop->id))->toBe(0)
        ->and($delivery->fresh()->status)->toBe('pending');
});
