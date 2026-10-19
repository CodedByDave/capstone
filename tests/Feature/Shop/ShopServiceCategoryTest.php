<?php

use App\Enums\AccountType;
use App\Models\Shop;
use App\Models\User;

function serviceCategoryOwner(): User
{
    $owner = User::factory()->create([
        'role' => AccountType::ShopOwner->value,
    ]);

    Shop::create([
        'owner_id' => $owner->id,
        'shop_name' => 'Category Test Laundry',
        'phone' => '09171234567',
        'municipality' => 'Imus',
        'barangay' => 'Poblacion',
        'postal_code' => '4103',
        'status' => 'active',
        'location_mode' => 'single',
        'setup_completed_at' => now(),
    ]);

    return $owner;
}

function serviceCategoryPayload(array $overrides = []): array
{
    return [
        'category' => 'dry_cleaning',
        'service_name' => 'Premium Dry Cleaning',
        'description' => 'Care for delicate garments.',
        'is_active' => true,
        'pricing_model' => 'per_kg',
        'price_per_kg' => 150,
        'estimated_hours' => 48,
        ...$overrides,
    ];
}

test('a shop can create a service with a supported category', function () {
    $owner = serviceCategoryOwner();

    $this->withoutMiddleware()
        ->actingAs($owner)
        ->post(route('shop.services.store'), serviceCategoryPayload())
        ->assertRedirect('/shop/operations/services')
        ->assertSessionHasNoErrors();

    $this->assertDatabaseHas('services_and_pricing', [
        'shop_id' => $owner->shop->id,
        'service_name' => 'Premium Dry Cleaning',
        'category' => 'dry_cleaning',
    ]);
});

test('a shop cannot create a service with an unsupported category', function () {
    $owner = serviceCategoryOwner();

    $this->withoutMiddleware()
        ->actingAs($owner)
        ->from('/shop/operations/services/create')
        ->post(route('shop.services.store'), serviceCategoryPayload([
            'category' => 'unsupported_category',
        ]))
        ->assertRedirect('/shop/operations/services/create')
        ->assertSessionHasErrors('category');

    $this->assertDatabaseMissing('services_and_pricing', [
        'service_name' => 'Premium Dry Cleaning',
    ]);
});
