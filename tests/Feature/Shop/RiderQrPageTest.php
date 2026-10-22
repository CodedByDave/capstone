<?php

use App\Enums\AccountType;
use App\Http\Middleware\EnforceOwnerPlatformPermissions;
use App\Http\Middleware\EnsureCurrentBusinessAgreementAccepted;
use App\Models\Delivery;
use App\Models\Shop;
use App\Models\User;

function qrDelivery(string $status = 'assigned'): Delivery
{
    $owner = User::factory()->create(['role' => AccountType::ShopOwner->value]);
    $shop = Shop::create([
        'owner_id' => $owner->id,
        'shop_name' => 'QR Laundry',
        'phone' => '09171234567',
        'municipality' => 'Imus',
        'barangay' => 'Poblacion',
        'postal_code' => '4103',
        'status' => 'active',
        'offers_delivery' => true,
        'setup_completed_at' => now(),
    ]);

    return Delivery::create([
        'shop_id' => $shop->id,
        'customer_name' => 'Maria Cruz',
        'customer_phone' => '09181234567',
        'delivery_address' => '12 Rizal St, Imus',
        'status' => $status,
    ]);
}

test('the rider QR link opens the rider page with the delivery, no login needed', function () {
    $delivery = qrDelivery();

    $this->get("/driver/{$delivery->driver_token}")
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('driver/DeliveryStatus')
            ->where('delivery.token', $delivery->driver_token)
            ->where('delivery.customer_name', 'Maria Cruz')
            ->where('delivery.delivery_address', '12 Rizal St, Imus')
            ->where('delivery.status', 'assigned'));
});

test('an unknown or mistyped rider token is a 404', function () {
    qrDelivery();

    $this->get('/driver/not-a-real-token')->assertNotFound();
    $this->patch('/driver/not-a-real-token/status', ['status' => 'picked_up'])->assertNotFound();
});

test('the rider moves the delivery forward from the page and cannot go backwards', function () {
    $delivery = qrDelivery('assigned');
    $url = "/driver/{$delivery->driver_token}/status";

    $this->patch($url, ['status' => 'picked_up'])->assertSessionHasNoErrors();
    expect($delivery->fresh())->status->toBe('picked_up')->picked_up_at->not->toBeNull();

    $this->patch($url, ['status' => 'delivered'])->assertSessionHasNoErrors();
    expect($delivery->fresh())->status->toBe('delivered')->delivered_at->not->toBeNull();

    $this->patch($url, ['status' => 'picked_up']);
    expect($delivery->fresh()->status)->toBe('delivered');
});

test('the rider can only set picked up, delivered or failed', function () {
    $delivery = qrDelivery('assigned');

    $this->patch("/driver/{$delivery->driver_token}/status", ['status' => 'pending'])
        ->assertSessionHasErrors('status');

    expect($delivery->fresh()->status)->toBe('assigned');
});

test('the QR code encodes the rider base URL from config, falling back to the app URL', function () {
    $this->withoutMiddleware([
        EnsureCurrentBusinessAgreementAccepted::class,
        EnforceOwnerPlatformPermissions::class,
    ]);
    $delivery = qrDelivery();
    $owner = $delivery->shop->owner;

    config(['app.url' => 'http://localhost:8000', 'app.rider_base_url' => 'http://192.168.18.4:8000/']);
    $this->actingAs($owner)->get('/shop/logistics')
        ->assertInertia(fn ($page) => $page->where('rider_base_url', 'http://192.168.18.4:8000'));

    config(['app.rider_base_url' => null]);
    $this->actingAs($owner)->get('/shop/logistics')
        ->assertInertia(fn ($page) => $page->where('rider_base_url', 'http://localhost:8000'));
});
