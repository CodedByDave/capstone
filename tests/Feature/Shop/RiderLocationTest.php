<?php

use App\Enums\AccountType;
use App\Models\Delivery;
use App\Models\Shop;
use App\Models\User;

function riderDelivery(string $status = 'assigned'): Delivery
{
    $owner = User::factory()->create(['role' => AccountType::ShopOwner->value]);
    $shop = Shop::create([
        'owner_id' => $owner->id,
        'shop_name' => 'Rider Laundry',
        'phone' => '09171234567',
        'municipality' => 'Imus',
        'barangay' => 'Poblacion',
        'postal_code' => '4103',
        'status' => 'active',
        'offers_delivery' => true,
        'delivery_pricing_set_at' => now(),
    ]);

    return Delivery::create([
        'shop_id' => $shop->id,
        'customer_name' => 'Customer',
        'delivery_address' => 'Anabu, Imus',
        'status' => $status,
    ]);
}

test('a rider shares a live location while the delivery is on the road', function (string $status) {
    $delivery = riderDelivery($status);

    $this->postJson("/driver/{$delivery->driver_token}/location", ['lat' => 14.4151, 'lng' => 120.9382])
        ->assertOk()
        ->assertJsonPath('message', 'Location updated.');

    $delivery->refresh();
    expect($delivery->rider_lat)->toBe(14.4151)
        ->and($delivery->rider_lng)->toBe(120.9382)
        ->and($delivery->rider_located_at)->not->toBeNull();
})->with(['assigned', 'picked_up']);

test('a rider location is rejected once the delivery is not on the road', function (string $status) {
    $delivery = riderDelivery($status);

    $this->postJson("/driver/{$delivery->driver_token}/location", ['lat' => 14.4, 'lng' => 120.9])
        ->assertStatus(409);

    expect($delivery->fresh()->rider_lat)->toBeNull();
})->with(['pending', 'delivered', 'failed']);

test('an unknown driver token is not found', function () {
    $this->postJson('/driver/not-a-real-token/location', ['lat' => 14.4, 'lng' => 120.9])
        ->assertNotFound();
});

test('rider coordinates are validated', function () {
    $delivery = riderDelivery();

    $this->postJson("/driver/{$delivery->driver_token}/location", ['lat' => 91, 'lng' => 'east'])
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['lat', 'lng']);
});
