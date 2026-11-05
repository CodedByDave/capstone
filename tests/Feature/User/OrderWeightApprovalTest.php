<?php

use App\Enums\AccountType;
use App\Http\Middleware\EnforceOwnerPlatformPermissions;
use App\Http\Middleware\EnsureCurrentBusinessAgreementAccepted;
use App\Models\Shop;
use App\Models\ShopOrder;
use App\Models\ShopService;
use App\Models\User;
use App\Services\ShopOrderService;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

beforeEach(function () {
    $this->withoutMiddleware([
        EnsureCurrentBusinessAgreementAccepted::class,
        EnforceOwnerPlatformPermissions::class,
    ]);
    Storage::fake('private');
});

function weightShop(): Shop
{
    $owner = User::factory()->create(['role' => AccountType::ShopOwner->value]);

    return Shop::create([
        'owner_id' => $owner->id,
        'shop_name' => 'Weigh Laundry',
        'phone' => '09171234567',
        'municipality' => 'Imus',
        'barangay' => 'Poblacion',
        'postal_code' => '4103',
        'status' => 'active',
        'setup_completed_at' => now(),
    ]);
}

function weightOrder(Shop $shop, ?User $customer, array $overrides = []): ShopOrder
{
    $service = ShopService::create([
        'shop_id' => $shop->id,
        'service_name' => 'Wash '.uniqid(),
        'is_active' => true,
        'pricing_model' => 'per_kg',
        'price_per_kg' => 60,
    ]);

    return ShopOrder::create(array_merge([
        'shop_id' => $shop->id,
        'user_id' => $customer?->id,
        'order_source' => $customer ? 'online' : 'shop',
        'service_id' => $service->id,
        'order_number' => 'ORD-'.strtoupper(uniqid()),
        'customer_name' => 'Customer',
        'pickup_type' => 'walk_in',
        'pricing_model' => 'per_kg',
        'price_per_kg' => 60,
        'estimated_weight_kg' => 3,
        'total_amount' => 180,
        'payment_method' => 'cash',
        'payment_status' => 'unpaid',
        'status' => 'pending',
    ], $overrides));
}

function weigh(ShopOrder $order, float $kg, ?UploadedFile $proof = null): ShopOrder
{
    return app(ShopOrderService::class)->updateOrder($order->fresh(), array_filter([
        'service_id' => $order->service_id,
        'actual_weight_kg' => $kg,
        'status' => $order->status,
        'weight_proof' => $proof,
    ], fn ($value) => $value !== null));
}

test('weighing an online order needs a proof photo', function () {
    $shop = weightShop();
    $order = weightOrder($shop, User::factory()->create());

    expect(fn () => weigh($order, 4))->toThrow(ValidationException::class);
    expect($order->fresh()->actual_weight_kg)->toBeNull();
});

test('a proof photo puts the online order awaiting the customer and keeps it from starting', function () {
    $shop = weightShop();
    $order = weightOrder($shop, User::factory()->create());

    $updated = weigh($order, 4, UploadedFile::fake()->image('scale.jpg'));

    expect($updated->weight_approval_status)->toBe('pending')
        ->and($updated->status)->toBe('pending')
        ->and($updated->priceIsConfirmed())->toBeFalse();
    Storage::disk('private')->assertExists($updated->weight_proof_path);

    expect(fn () => app(ShopOrderService::class)->updateStatus($updated, 'in_progress'))
        ->toThrow(ValidationException::class);
});

test('walk-in orders are weighed without a photo or approval', function () {
    $shop = weightShop();
    $order = weightOrder($shop, null);

    $updated = weigh($order, 4);

    expect($updated->weight_approval_status)->toBeNull()
        ->and($updated->priceIsConfirmed())->toBeTrue();
});

test('the customer approving the weight starts the order', function () {
    $shop = weightShop();
    $customer = User::factory()->create(['role' => AccountType::Customer->value]);
    $order = weightOrder($shop, $customer);
    $order = weigh($order, 4, UploadedFile::fake()->image('scale.jpg'));

    $this->actingAs($customer)
        ->post("/user/orders/{$order->payment_public_id}/weight/approve")
        ->assertRedirect();

    $order->refresh();
    expect($order->weight_approval_status)->toBe('approved')
        ->and($order->status)->toBe('in_progress');
});

test('the customer declining the weight cancels the order', function () {
    $shop = weightShop();
    $customer = User::factory()->create(['role' => AccountType::Customer->value]);
    $order = weigh(weightOrder($shop, $customer), 4, UploadedFile::fake()->image('scale.jpg'));

    $this->actingAs($customer)
        ->post("/user/orders/{$order->payment_public_id}/weight/decline")
        ->assertRedirect(route('user.orders.index'));

    $order->refresh();
    expect($order->weight_approval_status)->toBe('declined')
        ->and($order->status)->toBe('cancelled');
});

test('another customer cannot decide on or see the proof of an order', function () {
    $shop = weightShop();
    $customer = User::factory()->create(['role' => AccountType::Customer->value]);
    $other = User::factory()->create(['role' => AccountType::Customer->value]);
    $order = weigh(weightOrder($shop, $customer), 4, UploadedFile::fake()->image('scale.jpg'));

    $this->actingAs($other)->post("/user/orders/{$order->payment_public_id}/weight/approve")->assertNotFound();
    $this->actingAs($other)->post("/user/orders/{$order->payment_public_id}/weight/decline")->assertNotFound();
    $this->actingAs($other)->get("/user/orders/{$order->payment_public_id}/weight/proof")->assertNotFound();

    expect($order->fresh()->weight_approval_status)->toBe('pending');

    $this->actingAs($customer)->get("/user/orders/{$order->payment_public_id}/weight/proof")->assertOk();
});

test('a decision cannot be made twice', function () {
    $shop = weightShop();
    $customer = User::factory()->create(['role' => AccountType::Customer->value]);
    $order = weigh(weightOrder($shop, $customer), 4, UploadedFile::fake()->image('scale.jpg'));

    $this->actingAs($customer)->post("/user/orders/{$order->payment_public_id}/weight/approve");
    $this->actingAs($customer)
        ->post("/user/orders/{$order->payment_public_id}/weight/decline")
        ->assertSessionHasErrors('weight');

    expect($order->fresh()->status)->toBe('in_progress');
});

test('shop owner can view the proof of their own order but not another shops', function () {
    $shop = weightShop();
    $order = weigh(weightOrder($shop, User::factory()->create()), 4, UploadedFile::fake()->image('scale.jpg'));
    $otherShop = weightShop();

    $this->actingAs($shop->owner)->get("/shop/operations/orders/{$order->payment_public_id}/weight-proof")->assertOk();
    $this->actingAs($otherShop->owner)->get("/shop/operations/orders/{$order->payment_public_id}/weight-proof")->assertNotFound();
});
