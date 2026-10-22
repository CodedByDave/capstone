<?php

use App\Enums\AccountType;
use App\Models\Shop;
use App\Models\ShopOrder;
use App\Models\ShopService;
use App\Models\User;
use App\Services\OrderBillingBreakdownService;
use Inertia\Testing\AssertableInertia as Assert;

function billingShop(string $name = 'Billing Laundry', string $status = 'active'): Shop
{
    $owner = User::factory()->create(['role' => AccountType::ShopOwner->value]);

    return Shop::create([
        'owner_id' => $owner->id,
        'shop_name' => $name,
        'phone' => '09171234567',
        'municipality' => 'Imus',
        'barangay' => 'Poblacion',
        'postal_code' => '4103',
        'status' => $status,
        'location_mode' => 'single',
        'setup_completed_at' => now(),
    ]);
}

function billingService(Shop $shop, array $overrides = []): ShopService
{
    return ShopService::create(array_merge([
        'shop_id' => $shop->id,
        'service_name' => 'Wash and Fold',
        'is_active' => true,
        'pricing_model' => 'per_kg',
        'price_per_kg' => 60,
    ], $overrides));
}

function billingBundleService(Shop $shop, array $overrides = []): ShopService
{
    return billingService($shop, array_merge([
        'service_name' => 'Bundle Wash',
        'pricing_model' => 'per_bundle',
        'price_per_kg' => null,
        'bundle_weight_kg' => 5,
        'bundle_price' => 200,
    ], $overrides));
}

function billingOrder(User $customer, ShopService $service, array $overrides = []): ShopOrder
{
    return ShopOrder::create(array_merge([
        'shop_id' => $service->shop_id,
        'user_id' => $customer->id,
        'order_source' => 'online',
        'service_id' => $service->id,
        'order_number' => 'ONL-'.strtoupper(uniqid()),
        'customer_name' => $customer->name,
        'pickup_type' => 'walk_in',
        'pricing_model' => $service->pricing_model,
        'price_per_kg' => $service->price_per_kg,
        'bundle_weight_kg' => $service->bundle_weight_kg,
        'bundle_price' => $service->bundle_price,
        'estimated_weight_kg' => 2,
        'total_amount' => 120,
        'payment_status' => 'unpaid',
        'status' => 'pending',
    ], $overrides));
}

function billingCustomer(): User
{
    return User::factory()->create(['role' => AccountType::Customer->value]);
}

test('a customer sees an estimated breakdown on an order that is not weighed yet', function () {
    $customer = billingCustomer();
    $order = billingOrder($customer, billingService(billingShop()));

    $this->actingAs($customer)
        ->get(route('user.orders.show', $order->payment_public_id))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('user/OrderDetail')
            ->where('order.billing.is_estimate', true)
            ->where('order.billing.service.name', 'Wash and Fold')
            ->where('order.billing.service.quantity', 2)
            ->where('order.billing.service.unit', 'kg')
            ->where('order.billing.service.rate', 60)
            ->where('order.billing.service.amount', 120)
            ->where('order.billing.total', 120)
            ->where('order.billing.balance_due', 120));
});

test('a weighed order breakdown shows charges, discount, payment and balance', function () {
    $customer = billingCustomer();
    $order = billingOrder($customer, billingService(billingShop()), [
        'actual_weight_kg' => 3,
        'additional_charges' => 20,
        'discount_amount' => 10,
        'total_amount' => 190,
        'amount_paid' => 100,
        'payment_status' => 'partial',
    ]);

    $this->actingAs($customer)
        ->get(route('user.orders.show', $order->payment_public_id))
        ->assertInertia(fn (Assert $page) => $page
            ->where('order.billing.is_estimate', false)
            ->where('order.billing.service.quantity', 3)
            ->where('order.billing.service.amount', 180)
            ->where('order.billing.additional_charges', 20)
            ->where('order.billing.discount', 10)
            ->where('order.billing.total', 190)
            ->where('order.billing.amount_paid', 100)
            ->where('order.billing.balance_due', 90));
});

test('a per-bundle order breakdown uses the stored bundle count and rate', function () {
    $customer = billingCustomer();
    $order = billingOrder($customer, billingBundleService(billingShop()), [
        'estimated_weight_kg' => 6,
        'bundle_quantity' => 2,
        'total_amount' => 400,
    ]);

    $this->actingAs($customer)
        ->get(route('user.orders.show', $order->payment_public_id))
        ->assertInertia(fn (Assert $page) => $page
            ->where('order.billing.service.unit', 'bundle')
            ->where('order.billing.service.quantity', 2)
            ->where('order.billing.service.rate', 200)
            ->where('order.billing.service.bundle_weight_kg', 5)
            ->where('order.billing.service.amount', 400));
});

test('a balance due never goes below zero', function () {
    $customer = billingCustomer();
    $order = billingOrder($customer, billingService(billingShop()), [
        'total_amount' => 120,
        'amount_paid' => 150,
        'payment_status' => 'paid',
    ]);

    expect(app(OrderBillingBreakdownService::class)->forOrder($order)['balance_due'])->toBe(0.0);
});

test('a customer cannot see another customers order breakdown', function () {
    $owner = billingCustomer();
    $order = billingOrder($owner, billingService(billingShop()));

    $this->actingAs(billingCustomer())
        ->get(route('user.orders.show', $order->payment_public_id))
        ->assertNotFound();
});

test('a guest is sent to login instead of seeing the breakdown', function () {
    $customer = billingCustomer();
    $order = billingOrder($customer, billingService(billingShop()));

    $this->get(route('user.orders.show', $order->payment_public_id))
        ->assertRedirect(route('login'));
});

test('the estimate endpoint prices a per-kg service', function () {
    $shop = billingShop();
    $service = billingService($shop);

    $this->actingAs(billingCustomer())
        ->getJson(route('user.orders.estimate', [
            'shop_id' => $shop->id,
            'service_id' => $service->id,
            'estimated_weight_kg' => 2.5,
        ]))
        ->assertOk()
        ->assertJson([
            'is_estimate' => true,
            'service' => ['unit' => 'kg', 'quantity' => 2.5, 'rate' => 60, 'amount' => 150],
            'additional_charges' => 0,
            'discount' => 0,
            'total' => 150,
        ]);
});

test('the estimate endpoint rounds per-bundle services up to whole bundles', function () {
    $shop = billingShop();
    $service = billingBundleService($shop);

    $this->actingAs(billingCustomer())
        ->getJson(route('user.orders.estimate', [
            'shop_id' => $shop->id,
            'service_id' => $service->id,
            'estimated_weight_kg' => 6,
        ]))
        ->assertOk()
        ->assertJson([
            'service' => ['unit' => 'bundle', 'quantity' => 2, 'rate' => 200, 'amount' => 400],
            'total' => 400,
        ]);
});

test('the estimate endpoint hides inactive services, inactive shops and other shops services', function () {
    $shop = billingShop();
    $otherShop = billingShop('Other Laundry');
    $customer = billingCustomer();

    $inactive = billingService($shop, ['is_active' => false]);
    $foreign = billingService($otherShop);
    $pendingShop = billingShop('Pending Laundry', 'pending');
    $pendingService = billingService($pendingShop);

    foreach ([
        [$shop->id, $inactive->id],
        [$shop->id, $foreign->id],
        [$pendingShop->id, $pendingService->id],
        [$shop->id, 999999],
    ] as [$shopId, $serviceId]) {
        $this->actingAs($customer)
            ->getJson(route('user.orders.estimate', [
                'shop_id' => $shopId,
                'service_id' => $serviceId,
                'estimated_weight_kg' => 2,
            ]))
            ->assertNotFound();
    }
});

test('the estimate endpoint validates the weight', function () {
    $shop = billingShop();
    $service = billingService($shop);
    $customer = billingCustomer();

    foreach ([0.1, 101, 'abc', null] as $weight) {
        $this->actingAs($customer)
            ->getJson(route('user.orders.estimate', [
                'shop_id' => $shop->id,
                'service_id' => $service->id,
                'estimated_weight_kg' => $weight,
            ]))
            ->assertUnprocessable()
            ->assertJsonValidationErrors('estimated_weight_kg');
    }
});

test('the estimate endpoint requires a signed in customer', function () {
    $shop = billingShop();
    $service = billingService($shop);
    $query = ['shop_id' => $shop->id, 'service_id' => $service->id, 'estimated_weight_kg' => 2];

    $this->getJson(route('user.orders.estimate', $query))->assertUnauthorized();

    $owner = User::factory()->create(['role' => AccountType::ShopOwner->value]);
    $this->actingAs($owner)->get(route('user.orders.estimate', $query))->assertRedirect();
});

test('an order placed by a customer stores the same estimate the breakdown quotes', function () {
    $shop = billingShop();
    $service = billingBundleService($shop);
    $quoted = app(OrderBillingBreakdownService::class)->estimate($service, 6);

    $order = app(\App\Services\ShopOrderService::class)->createCustomerOrder(
        billingCustomer(),
        [
            'shop_id' => $shop->id,
            'service_id' => $service->id,
            'customer_name' => 'Quote Customer',
            'customer_phone' => '09171234567',
            'estimated_weight_kg' => 6,
            'pickup_type' => 'walk_in',
            'payment_method' => 'cash',
            'customer_agreement_version' => config('customer_service_agreement.version'),
        ],
        '127.0.0.1',
        'Pest',
    );

    expect((float) $order->total_amount)->toBe($quoted['total'])
        ->and((int) $order->bundle_quantity)->toBe(2);
});

test('a breakdown lists the delivery fee as its own line and keeps the balance due', function () {
    $customer = billingCustomer();
    $order = billingOrder($customer, billingService(billingShop()), [
        'actual_weight_kg' => 3,
        'additional_charges' => 20,
        'discount_amount' => 10,
        'delivery_fee' => 45,
        'total_amount' => 235,
        'amount_paid' => 190,
        'payment_status' => 'partial',
    ]);

    $breakdown = app(OrderBillingBreakdownService::class)->forOrder($order);

    expect($breakdown['service']['amount'])->toBe(180.0)
        ->and($breakdown['additional_charges'])->toBe(20.0)
        ->and($breakdown['discount'])->toBe(10.0)
        ->and($breakdown['delivery_fee'])->toBe(45.0)
        ->and($breakdown['total'])->toBe(235.0)
        ->and($breakdown['balance_due'])->toBe(45.0);
});

test('an unweighed estimate breakdown reports no delivery fee', function () {
    $customer = billingCustomer();
    $order = billingOrder($customer, billingService(billingShop()));

    expect(app(OrderBillingBreakdownService::class)->forOrder($order)['delivery_fee'])->toBe(0.0);
});
