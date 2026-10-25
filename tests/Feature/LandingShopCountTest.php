<?php

use App\Enums\AccountType;
use App\Models\Shop;
use App\Models\User;
use Inertia\Testing\AssertableInertia as Assert;

function landingShop(string $status): Shop
{
    $owner = User::factory()->create(['role' => AccountType::ShopOwner->value]);

    return Shop::create([
        'owner_id' => $owner->id,
        'shop_name' => 'Landing Laundry',
        'phone' => '09171234567',
        'municipality' => 'Imus',
        'barangay' => 'Poblacion',
        'postal_code' => '4103',
        'status' => $status,
    ]);
}

it('passes the number of active shops to the landing page', function () {
    landingShop('active');
    landingShop('active');
    landingShop('pending');

    $this->get('/')->assertOk()->assertInertia(fn (Assert $page) => $page
        ->component('Landing')
        ->where('shopCount', 2));
});
