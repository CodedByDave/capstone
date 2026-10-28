<?php

use App\Enums\AccountType;
use App\Models\Shop;
use App\Models\ShopOrder;
use App\Models\ShopService;
use App\Models\User;
use App\Services\ShopPaymentSettingsService;
use Illuminate\Support\Facades\Http;
use Inertia\Testing\AssertableInertia as Assert;

function demoPaymentOrder(): array
{
    config()->set('services.paymongo.secret_key', 'sk_test_platform');
    config()->set('services.paymongo.demo_enabled', true);
    config()->set('services.paymongo.demo_secret_key', 'sk_test_demoLaundryHubPanel2026');
    config()->set('services.paymongo.demo_public_key', 'pk_test_demoLaundryHubPanel2026');

    $owner = User::factory()->create(['role' => AccountType::ShopOwner->value]);
    $customer = User::factory()->create(['role' => AccountType::Customer->value]);
    $shop = Shop::create([
        'owner_id' => $owner->id,
        'shop_name' => 'Panelist Demo Laundry',
        'phone' => '09171234567',
        'municipality' => 'Imus',
        'barangay' => 'Anabu',
        'postal_code' => '4103',
        'status' => 'active',
        'paymongo_secret_key' => 'sk_test_demoLaundryHubPanel2026',
        'paymongo_public_key' => 'pk_test_demoLaundryHubPanel2026',
    ]);
    $customer->update(['referred_shop_id' => $shop->id]);
    $service = ShopService::create([
        'shop_id' => $shop->id,
        'service_name' => 'Wash and Fold',
        'pricing_model' => 'per_kg',
        'price_per_kg' => 50,
    ]);
    $order = ShopOrder::create([
        'shop_id' => $shop->id,
        'user_id' => $customer->id,
        'service_id' => $service->id,
        'order_number' => 'ORD-DEMO-001',
        'customer_name' => $customer->name,
        'customer_phone' => '09171234567',
        'pricing_model' => 'per_kg',
        'estimated_weight_kg' => 5,
        'actual_weight_kg' => 5,
        'price_per_kg' => 50,
        'total_amount' => 250,
        'payment_method' => 'gcash',
        'payment_status' => 'unpaid',
        'status' => 'in_progress',
    ]);

    return compact('owner', 'customer', 'shop', 'order');
}

function fakePaymongoHostedCheckout(): void
{
    Http::fake([
        'https://api.paymongo.com/v1/checkout_sessions' => Http::response([
            'data' => [
                'id' => 'cs_test_customer_order',
                'attributes' => [
                    'checkout_url' => 'https://checkout.paymongo.test/customer-order',
                ],
            ],
        ]),
        'https://api.paymongo.com/v1/checkout_sessions/cs_test_customer_order' => Http::response([
            'data' => [
                'id' => 'cs_test_customer_order',
                'attributes' => [
                    'status' => 'active',
                    'payment_intent' => [
                        'attributes' => [
                            'status' => 'succeeded',
                            'payments' => [['id' => 'pay_test_customer_order']],
                        ],
                    ],
                ],
            ],
        ]),
    ]);
}

test('the configured demo keys are accepted without validating them against PayMongo', function () {
    Http::fake();
    ['owner' => $owner, 'shop' => $shop] = demoPaymentOrder();

    $result = app(ShopPaymentSettingsService::class)->update($owner, [
        'paymongo_secret_key' => 'sk_test_demoLaundryHubPanel2026',
        'paymongo_public_key' => 'pk_test_demoLaundryHubPanel2026',
    ]);

    expect($result['message'])->toContain('PayMongo test checkout')
        ->and($shop->refresh()->paymongo_secret_key)->toBe('sk_test_demoLaundryHubPanel2026');
    Http::assertNothingSent();
});

test('demo shop keys redirect a customer to the hosted PayMongo test checkout', function () {
    fakePaymongoHostedCheckout();
    ['customer' => $customer, 'order' => $order] = demoPaymentOrder();

    $this->actingAs($customer)
        ->withHeader('X-Inertia', 'true')
        ->post(route('user.orders.pay-online', $order->payment_public_id), [
            'payment_method' => 'gcash',
        ])
        ->assertStatus(409)
        ->assertHeader('X-Inertia-Location', 'https://checkout.paymongo.test/customer-order');

    $order->refresh();
    expect($order->paymongo_session_id)->toBe('cs_test_customer_order')
        ->and($order->payment_public_id)->toHaveLength(26)
        ->and(route('user.orders.payment.receipt', $order->payment_public_id))->toContain($order->payment_public_id)
        ->and(route('user.orders.payment.receipt', $order->payment_public_id))->not->toContain("/orders/{$order->id}/")
        ->and($order->payment_method)->toBe('gcash')
        ->and($order->payment_status)->toBe('unpaid');

    Http::assertSent(function ($request) use ($customer, $order) {
        return $request->url() === 'https://api.paymongo.com/v1/checkout_sessions'
            && $request->hasHeader('Authorization', 'Basic '.base64_encode('sk_test_platform:'))
            && data_get($request->data(), 'data.attributes.payment_method_types') === ['gcash']
            && data_get($request->data(), 'data.attributes.billing.email') === $customer->email
            && data_get($request->data(), 'data.attributes.billing.name') === $order->customer_name
            && data_get($request->data(), 'data.attributes.billing.phone') === '9171234567'
            && data_get($request->data(), 'data.attributes.send_email_receipt') === true;
    });
});

test('customer can switch the online payment method before checkout', function () {
    fakePaymongoHostedCheckout();
    ['customer' => $customer, 'order' => $order] = demoPaymentOrder();

    $this->actingAs($customer)
        ->withHeader('X-Inertia', 'true')
        ->post(route('user.orders.pay-online', $order->payment_public_id), [
            'payment_method' => 'maya',
        ])
        ->assertStatus(409)
        ->assertHeader('X-Inertia-Location', 'https://checkout.paymongo.test/customer-order');

    expect($order->refresh()->payment_method)->toBe('maya');
    Http::assertSent(fn ($request) => data_get($request->data(), 'data.attributes.payment_method_types') === ['paymaya']
    );
});

test('an existing local demo checkout continues to PayMongo instead of marking the order paid', function () {
    fakePaymongoHostedCheckout();
    ['customer' => $customer, 'order' => $order] = demoPaymentOrder();
    $order->update(['paymongo_session_id' => 'cs_demo_old_session']);

    $this->actingAs($customer)
        ->withHeader('X-Inertia', 'true')
        ->post(route('user.orders.payment.demo.complete', $order->payment_public_id), [
            'payment_method' => 'gcash',
        ])
        ->assertStatus(409)
        ->assertHeader('X-Inertia-Location', 'https://checkout.paymongo.test/customer-order');

    $order->refresh();
    expect($order->paymongo_session_id)->toBe('cs_test_customer_order')
        ->and($order->payment_status)->toBe('unpaid')
        ->and($order->paymongo_payment_id)->toBeNull();
});

test('returning from hosted test checkout verifies payment and opens the invoice receipt', function () {
    fakePaymongoHostedCheckout();
    ['customer' => $customer, 'order' => $order] = demoPaymentOrder();

    $this->actingAs($customer)->post(route('user.orders.pay-online', $order->payment_public_id), [
        'payment_method' => 'gcash',
    ]);

    $this->actingAs($customer)
        ->get(route('user.orders.payment.success', $order->payment_public_id))
        ->assertRedirect(route('user.orders.payment.receipt', $order->payment_public_id));

    $order->refresh();
    expect($order->payment_status)->toBe('paid')
        ->and((float) $order->amount_paid)->toBe(250.0)
        ->and($order->paymongo_payment_id)->toBe('pay_test_customer_order')
        ->and($order->paid_at)->not->toBeNull();

    $this->actingAs($customer)
        ->get(route('user.orders.payment.receipt', $order->payment_public_id))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('user/payment/Receipt')
            ->where('receipt.order_number', 'ORD-DEMO-001')
            ->where('receipt.amount_paid', 250)
            ->where('receipt.is_demo', true));

    $this->actingAs($customer)
        ->get("/user/orders/{$order->id}/payment/receipt")
        ->assertNotFound();
});

test('placing a per kilogram order waits for the shop weight before checkout', function () {
    fakePaymongoHostedCheckout();
    ['customer' => $customer, 'shop' => $shop] = demoPaymentOrder();
    $service = ShopService::query()->where('shop_id', $shop->id)->firstOrFail();

    $response = $this->actingAs($customer)
        ->withHeader('X-Inertia', 'true')
        ->post(route('user.orders.store'), [
            'shop_id' => $shop->id,
            'service_id' => $service->id,
            'customer_name' => $customer->name,
            'customer_phone' => '09171234567',
            'estimated_weight_kg' => 3,
            'pickup_type' => 'walk_in',
            'payment_method' => 'gcash',
            'customer_address' => null,
            'special_instructions' => null,
            'customer_agreement_version' => config('customer_service_agreement.version'),
            'customer_agreement_accepted' => true,
        ]);

    $placedOrder = ShopOrder::query()
        ->where('user_id', $customer->id)
        ->where('order_number', '!=', 'ORD-DEMO-001')
        ->latest('id')
        ->firstOrFail();

    $response->assertRedirect(route('user.orders.index'));
    expect($placedOrder->actual_weight_kg)->toBeNull()
        ->and($placedOrder->paymongo_session_id)->toBeNull()
        ->and((float) $placedOrder->total_amount)->toBe(150.0);
});

test('placing a bundle order also waits for shop weight and estimates bundle count', function () {
    fakePaymongoHostedCheckout();
    ['customer' => $customer, 'shop' => $shop] = demoPaymentOrder();
    $bundleService = ShopService::create([
        'shop_id' => $shop->id,
        'service_name' => 'Bundle Wash',
        'pricing_model' => 'per_bundle',
        'bundle_weight_kg' => 5,
        'bundle_price' => 190,
    ]);

    $response = $this->actingAs($customer)
        ->withHeader('X-Inertia', 'true')
        ->post(route('user.orders.store'), [
            'shop_id' => $shop->id,
            'service_id' => $bundleService->id,
            'customer_name' => $customer->name,
            'customer_phone' => '09171234567',
            'estimated_weight_kg' => 10,
            'pickup_type' => 'walk_in',
            'payment_method' => 'gcash',
            'customer_address' => null,
            'special_instructions' => null,
            'customer_agreement_version' => config('customer_service_agreement.version'),
            'customer_agreement_accepted' => true,
        ]);

    $placedOrder = ShopOrder::query()
        ->where('service_id', $bundleService->id)
        ->latest('id')
        ->firstOrFail();

    $response->assertRedirect(route('user.orders.index'));
    expect($placedOrder->actual_weight_kg)->toBeNull()
        ->and($placedOrder->bundle_quantity)->toBe(2)
        ->and((float) $placedOrder->total_amount)->toBe(380.0)
        ->and($placedOrder->paymongo_session_id)->toBeNull();

    $this->actingAs($customer)
        ->post(route('user.orders.pay-online', $placedOrder->payment_public_id), [
            'payment_method' => 'gcash',
        ])
        ->assertRedirect();

    Http::assertNothingSent();
});

test('per kilogram payment remains locked until the shop records actual weight', function () {
    fakePaymongoHostedCheckout();
    ['customer' => $customer, 'order' => $order] = demoPaymentOrder();
    $order->update(['actual_weight_kg' => null]);

    $this->actingAs($customer)
        ->post(route('user.orders.pay-online', $order->payment_public_id), [
            'payment_method' => 'gcash',
        ])
        ->assertRedirect();

    expect($order->refresh()->paymongo_session_id)->toBeNull();
    Http::assertNothingSent();
});

test('shop actual weight recalculates the final amount and unlocks checkout', function () {
    fakePaymongoHostedCheckout();
    ['customer' => $customer, 'order' => $order] = demoPaymentOrder();
    $order->update(['actual_weight_kg' => null, 'total_amount' => 200]);

    app(App\Services\ShopOrderService::class)->updateOrder($order, [
        'service_id' => $order->service_id,
        'estimated_weight_kg' => 4,
        'actual_weight_kg' => 4.5,
        'bundle_quantity' => null,
        'additional_charges' => 20,
        'discount_amount' => 5,
        'amount_paid' => 0,
        'supplies' => [],
    ]);

    $order->refresh();
    expect((float) $order->actual_weight_kg)->toBe(4.5)
        ->and((float) $order->total_amount)->toBe(240.0)
        ->and($order->payment_status)->toBe('unpaid')
        ->and($customer->notifications()->where('data->type', 'weight_confirmed')->exists())->toBeTrue();

    $this->actingAs($customer)
        ->withHeader('X-Inertia', 'true')
        ->post(route('user.orders.pay-online', $order->payment_public_id), [
            'payment_method' => 'gcash',
        ])
        ->assertStatus(409)
        ->assertHeader('X-Inertia-Location', 'https://checkout.paymongo.test/customer-order');
});

test('a customer cannot verify another customers hosted payment session', function () {
    fakePaymongoHostedCheckout();
    ['customer' => $customer, 'order' => $order] = demoPaymentOrder();
    $otherCustomer = User::factory()->create(['role' => AccountType::Customer->value]);

    $this->actingAs($customer)->post(route('user.orders.pay-online', $order->payment_public_id), [
        'payment_method' => 'gcash',
    ]);

    $this->actingAs($otherCustomer)
        ->get(route('user.orders.payment.success', $order->payment_public_id))
        ->assertNotFound();

    $this->actingAs($otherCustomer)
        ->get(route('user.orders.payment.receipt', $order->payment_public_id))
        ->assertNotFound();

    $this->actingAs($customer)
        ->get("/user/orders/{$order->id}")
        ->assertNotFound();

    $this->actingAs($customer)
        ->get(route('user.orders.show', $order->payment_public_id))
        ->assertOk();

    $this->actingAs($otherCustomer)
        ->get(route('user.orders.show', $order->payment_public_id))
        ->assertNotFound();
});
