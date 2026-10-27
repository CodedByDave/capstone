<?php

use App\Enums\AccountType;
use App\Http\Requests\Shop\Operations\StoreShopOrderRequest;
use App\Http\Requests\Shop\Operations\UpdateShopOrderRequest;
use App\Models\Promotion;
use App\Models\Shop;
use App\Models\User;

function promoShop(): Shop
{
    $owner = User::factory()->create(['role' => AccountType::ShopOwner->value]);

    return Shop::create([
        'owner_id' => $owner->id,
        'shop_name' => 'Promo Laundry '.$owner->id,
        'phone' => '09171234567',
        'municipality' => 'Imus',
        'barangay' => 'Poblacion',
        'postal_code' => '4103',
        'status' => 'active',
        'setup_completed_at' => now(),
    ]);
}

function promoFor(Shop $shop): Promotion
{
    return Promotion::create([
        'shop_id' => $shop->id,
        'name' => 'Summer Sale',
        'type' => 'percentage',
        'value' => 10,
        'is_active' => true,
    ]);
}

/** Validates only the promotion field the way the given order request would for this shop's staff. */
function promotionValidation(string $class, Shop $actingShop, int $promotionId)
{
    $request = new $class;
    $request->setUserResolver(fn () => $actingShop->owner);

    return validator(['promotion_id' => $promotionId], ['promotion_id' => $request->rules()['promotion_id']]);
}

dataset('order requests with a promotion', [
    'staff create' => [StoreShopOrderRequest::class],
    'staff edit' => [UpdateShopOrderRequest::class],
]);

it('keeps the promotion an order was given', function (string $class) {
    $shop = promoShop();
    $promotion = promoFor($shop);

    $validator = promotionValidation($class, $shop, $promotion->id);

    expect($validator->passes())->toBeTrue()
        ->and($validator->validated())->toBe(['promotion_id' => $promotion->id]);
})->with('order requests with a promotion');

it('refuses a promotion that belongs to another shop', function (string $class) {
    $shop = promoShop();
    $other = promoShop();

    expect(promotionValidation($class, $shop, promoFor($other)->id)->passes())->toBeFalse();
})->with('order requests with a promotion');
