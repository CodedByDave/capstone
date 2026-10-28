<?php

use App\Enums\AccountType;
use App\Models\Shop;
use App\Models\ShopOrder;
use App\Models\ShopService;
use App\Models\User;

function backfillShop(string $name): Shop
{
    $owner = User::factory()->create(['role' => AccountType::ShopOwner->value]);

    return Shop::create([
        'owner_id' => $owner->id,
        'shop_name' => $name,
        'phone' => '09171234567',
        'municipality' => 'Imus',
        'barangay' => 'Poblacion',
        'postal_code' => '4103',
        'status' => 'active',
    ]);
}

function backfillOrder(Shop $shop, User $customer): void
{
    $service = ShopService::create([
        'shop_id' => $shop->id,
        'service_name' => 'Wash',
        'is_active' => true,
        'pricing_model' => 'per_kg',
        'price_per_kg' => 60,
    ]);

    ShopOrder::create([
        'shop_id' => $shop->id,
        'user_id' => $customer->id,
        'order_source' => 'online',
        'service_id' => $service->id,
        'order_number' => 'ONL-'.strtoupper(uniqid()),
        'customer_name' => $customer->name,
        'customer_phone' => '09181234567',
        'pickup_type' => 'walk_in',
        'pricing_model' => 'per_kg',
        'price_per_kg' => 60,
        'estimated_weight_kg' => 3,
        'total_amount' => 180,
        'payment_status' => 'unpaid',
        'status' => 'pending',
    ]);
}

test('existing customers are linked to the shop they ordered from most', function () {
    $often = backfillShop('Often');
    $once = backfillShop('Once');
    $customer = User::factory()->create(['role' => AccountType::Customer->value]);
    $newcomer = User::factory()->create(['role' => AccountType::Customer->value]);
    $linked = User::factory()->create(['role' => AccountType::Customer->value, 'referred_shop_id' => $once->id]);

    backfillOrder($often, $customer);
    backfillOrder($often, $customer);
    backfillOrder($once, $customer);
    backfillOrder($often, $linked);

    (require database_path('migrations/2026_11_07_000002_backfill_customer_referred_shop.php'))->up();

    expect($customer->fresh()->referred_shop_id)->toBe($often->id)
        ->and($newcomer->fresh()->referred_shop_id)->toBeNull()
        ->and($linked->fresh()->referred_shop_id)->toBe($once->id);
});
