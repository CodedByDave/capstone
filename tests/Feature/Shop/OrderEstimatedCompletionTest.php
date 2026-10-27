<?php

use App\Enums\AccountType;
use App\Models\Shop;
use App\Models\ShopOrderAutomationSetting;
use App\Models\ShopService;
use App\Models\User;
use App\Services\CustomerAgreementService;
use App\Services\ShopOrderService;
use Illuminate\Support\Carbon;

function etaShop(): Shop
{
    $owner = User::factory()->create(['role' => AccountType::ShopOwner->value]);

    return Shop::create([
        'owner_id' => $owner->id,
        'shop_name' => 'Eta Laundry '.$owner->id,
        'phone' => '09171234567',
        'municipality' => 'Imus',
        'barangay' => 'Poblacion',
        'postal_code' => '4103',
        'status' => 'active',
        'setup_completed_at' => now(),
    ]);
}

function etaCreateOrder(Shop $shop, array $overrides = [])
{
    $service = ShopService::create([
        'shop_id' => $shop->id, 'service_name' => 'Wash & Fold', 'is_active' => true,
        'pricing_model' => 'per_kg', 'price_per_kg' => 60,
    ]);

    return app(ShopOrderService::class)->createOrder(array_merge([
        'shop_id' => $shop->id,
        'customer_name' => 'Maria S.',
        'service_id' => $service->id,
        'pickup_type' => 'walk_in',
        'pricing_model' => 'per_kg',
        'price_per_kg' => 60,
        'actual_weight_kg' => 3,
        'total_amount' => 180,
        'amount_paid' => 0,
        'customer_agreement_version' => app(CustomerAgreementService::class)->agreementDataForShopId($shop->id)['version'],
        'customer_agreement_accepted' => true,
    ], $overrides), $shop->owner);
}

afterEach(fn () => Carbon::setTestNow());

it('uses the shop automation wait, or the 120 minute default', function () {
    $shop = etaShop();
    $orders = app(ShopOrderService::class);

    expect($orders->defaultCompletionMinutes($shop->id))->toBe(120);

    ShopOrderAutomationSetting::create([
        'shop_id' => $shop->id,
        ...ShopOrderAutomationSetting::defaults(),
        'auto_complete_grace_minutes' => 45,
    ]);

    expect($orders->defaultCompletionMinutes($shop->id))->toBe(45);
});

it('sets the estimated completion from the wait when an order is created, whatever is sent', function () {
    Carbon::setTestNow('2026-10-27 15:13:00');
    $shop = etaShop();

    $order = etaCreateOrder($shop, ['estimated_completion_at' => '2020-01-01 00:00:00']);

    expect($order->fresh()->estimated_completion_at->format('Y-m-d H:i'))->toBe('2026-10-27 17:13');

    ShopOrderAutomationSetting::create([
        'shop_id' => $shop->id,
        ...ShopOrderAutomationSetting::defaults(),
        'auto_complete_grace_minutes' => 45,
    ]);

    expect(etaCreateOrder($shop)->fresh()->estimated_completion_at->format('Y-m-d H:i'))->toBe('2026-10-27 15:58');
});
