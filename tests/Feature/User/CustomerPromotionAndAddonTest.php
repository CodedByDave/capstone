<?php

use App\Enums\AccountType;
use App\Models\Inventory;
use App\Models\Promotion;
use App\Models\Shop;
use App\Models\ShopService;
use App\Models\User;
use App\Services\CustomerShopCatalogService;
use App\Services\OrderBillingBreakdownService;
use Illuminate\Validation\ValidationException;

function promoShop(): array
{
    $owner = User::factory()->create(['role' => AccountType::ShopOwner->value]);
    $shop = Shop::create([
        'owner_id' => $owner->id,
        'shop_name' => 'Promo Laundry '.$owner->id,
        'phone' => '09171234567',
        'municipality' => 'Imus',
        'barangay' => 'Poblacion',
        'postal_code' => '4103',
        'status' => 'active',
        'setup_completed_at' => now(),
    ]);
    $service = ShopService::create([
        'shop_id' => $shop->id,
        'service_name' => 'Wash & Fold',
        'is_active' => true,
        'pricing_model' => 'per_kg',
        'price_per_kg' => 60,
    ]);

    return [$shop, $service];
}

function promo(Shop $shop, array $overrides = []): Promotion
{
    return Promotion::create(array_merge([
        'shop_id' => $shop->id,
        'name' => '10% off',
        'type' => 'percentage',
        'value' => 10,
        'is_active' => true,
    ], $overrides));
}

it('applies a chosen promotion to the customer estimate', function () {
    [$shop, $service] = promoShop();
    $promotion = promo($shop);

    $estimate = app(OrderBillingBreakdownService::class)->estimate($service, 5, null, $promotion->id);

    expect($estimate['discount'])->toBe(30.0)
        ->and($estimate['total'])->toBe(270.0);
});

it('rejects a promotion below its minimum, from another shop, or switched off', function () {
    [$shop, $service] = promoShop();
    [$otherShop] = promoShop();
    $billing = app(OrderBillingBreakdownService::class);

    $minimum = promo($shop, ['min_order_amount' => 500]);
    $foreign = promo($otherShop);
    $off = promo($shop, ['is_active' => false]);

    foreach ([$minimum, $foreign, $off] as $promotion) {
        expect(fn () => $billing->estimate($service, 5, null, $promotion->id))
            ->toThrow(ValidationException::class);
    }
});

it('lists only enabled add-on items with their price for customers', function () {
    [$shop] = promoShop();
    $base = ['shop_id' => $shop->id, 'unit' => 'ml', 'quantity' => 1000, 'min_stock' => 1, 'unit_price' => 0.2, 'status' => 'active'];
    Inventory::create([...$base, 'name' => 'Softener', 'addon_enabled' => true, 'addon_name' => 'Extra softener', 'addon_quantity' => 30, 'addon_price' => 15]);
    Inventory::create([...$base, 'name' => 'Hidden', 'addon_enabled' => false]);

    $options = app(CustomerShopCatalogService::class)->additionalChargeOptions($shop->id);

    expect($options)->toHaveCount(1)
        ->and($options[0])->toMatchArray(['name' => 'Extra softener', 'quantity' => 30, 'unit' => 'ml', 'price' => 15.0]);
});
