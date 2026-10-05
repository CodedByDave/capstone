<?php

use App\Enums\AccountType;
use App\Models\Shop;
use App\Models\ShopOrder;
use App\Models\ShopService;
use App\Models\User;

function customerAgreementScenario(): array
{
    $owner = User::factory()->create(['role' => AccountType::ShopOwner->value]);
    $customer = User::factory()->create(['role' => AccountType::Customer->value]);
    $shop = Shop::create([
        'owner_id' => $owner->id,
        'shop_name' => 'Careful Laundry',
        'phone' => '09171234567',
        'municipality' => 'Imus',
        'barangay' => 'Poblacion',
        'postal_code' => '4103',
        'status' => 'active',
        'location_mode' => 'single',
        'offers_pickup' => true,
        'offers_delivery' => false,
        'setup_completed_at' => now(),
    ]);
    $service = ShopService::create([
        'shop_id' => $shop->id,
        'service_name' => 'Wash and Fold',
        'is_active' => true,
        'pricing_model' => 'per_kg',
        'price_per_kg' => 60,
    ]);

    return compact('customer', 'shop', 'service');
}

function customerOrderPayload(Shop $shop, ShopService $service): array
{
    return [
        'shop_id' => $shop->id,
        'service_id' => $service->id,
        'customer_name' => 'Test Customer',
        'customer_phone' => '09171234567',
        'estimated_weight_kg' => 2,
        'pickup_type' => 'walk_in',
        'payment_method' => 'cash',
        'customer_agreement_version' => config('customer_service_agreement.version'),
    ];
}

test('customer cannot place an order without accepting the service agreement', function () {
    ['customer' => $customer, 'shop' => $shop, 'service' => $service] = customerAgreementScenario();

    $this->actingAs($customer)
        ->post(route('user.orders.store'), customerOrderPayload($shop, $service))
        ->assertSessionHasErrors('customer_agreement_accepted');

    $this->assertDatabaseCount('shop_orders', 0);
    $this->assertDatabaseCount('customer_agreement_acceptances', 0);
});

test('accepted agreement is stored as an immutable order snapshot', function () {
    ['customer' => $customer, 'shop' => $shop, 'service' => $service] = customerAgreementScenario();

    $this->actingAs($customer)
        ->withHeader('User-Agent', 'Agreement Feature Test')
        ->post(route('user.orders.store'), [
            ...customerOrderPayload($shop, $service),
            'customer_agreement_accepted' => true,
        ])
        ->assertRedirect(route('user.orders.index'));

    $order = ShopOrder::query()->where('user_id', $customer->id)->firstOrFail();
    $acceptance = $order->customerAgreementAcceptance()->firstOrFail();

    expect($acceptance->agreement_version)->toBe(config('customer_service_agreement.version'))
        ->and($acceptance->content_hash)->toBe(hash('sha256', $acceptance->agreement_content))
        ->and($acceptance->agreement_content)->toContain('SHOP-CAUSED LOSS OR DAMAGE')
        ->and($acceptance->agreement_content)->toContain($shop->shop_name)
        ->and($acceptance->acceptance_method)->toBe('online_checkbox')
        ->and($acceptance->user_agent)->toBe('Agreement Feature Test');
});
