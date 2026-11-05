<?php

use App\Enums\AccountType;
use App\Models\Shop;
use App\Models\ShopService;
use App\Models\User;
use App\Services\ShopOrderService;
use Illuminate\Validation\ValidationException;

function closedShopFixture(array $shopOverrides = []): array
{
    $owner = User::factory()->create(['role' => AccountType::ShopOwner->value]);
    $shop = Shop::create(array_merge([
        'owner_id' => $owner->id,
        'shop_name' => 'Hours Laundry '.$owner->id,
        'phone' => '09171234567',
        'municipality' => 'Imus',
        'barangay' => 'Poblacion',
        'postal_code' => '4103',
        'status' => 'active',
        'setup_completed_at' => now(),
    ], $shopOverrides));
    $service = ShopService::create([
        'shop_id' => $shop->id,
        'service_name' => 'Wash & Fold',
        'is_active' => true,
        'pricing_model' => 'per_kg',
        'price_per_kg' => 60,
    ]);
    $customer = User::factory()->create(['role' => AccountType::Customer->value, 'referred_shop_id' => $shop->id]);

    return [$shop, $service, $customer];
}

function placeWalkInOrder(Shop $shop, ShopService $service, User $customer): void
{
    app(ShopOrderService::class)->createCustomerOrder($customer, [
        'shop_id' => $shop->id,
        'service_id' => $service->id,
        'customer_name' => 'Test Customer',
        'customer_phone' => '09171234567',
        'estimated_weight_kg' => 3,
        'pickup_type' => 'walk_in',
        'customer_agreement_version' => app(\App\Services\CustomerAgreementService::class)->agreementData($shop)['version'],
    ], null, null);
}

it('refuses a customer order on a day the shop is closed', function () {
    // The test clock is today at noon; open only on the other six days.
    $today = now()->dayOfWeek;
    [$shop, $service, $customer] = closedShopFixture(['operating_days' => array_values(array_diff(range(0, 6), [$today]))]);

    expect(fn () => placeWalkInOrder($shop, $service, $customer))->toThrow(ValidationException::class);
    expect(\App\Models\ShopOrder::where("shop_id", $shop->id)->count())->toBe(0);
});

it('refuses a customer order outside the opening hours', function () {
    [$shop, $service, $customer] = closedShopFixture(['is_open_24_7' => false, 'opens_at' => '06:00:00', 'closes_at' => '07:00:00']);

    expect(fn () => placeWalkInOrder($shop, $service, $customer))->toThrow(ValidationException::class);
});

it('accepts a customer order while the shop is open', function () {
    [$shop, $service, $customer] = closedShopFixture(['is_open_24_7' => true]);

    placeWalkInOrder($shop, $service, $customer);

    expect(\App\Models\ShopOrder::where("shop_id", $shop->id)->count())->toBe(1);
});
