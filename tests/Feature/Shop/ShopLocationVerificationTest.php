<?php

use App\Enums\AccountType;
use App\Http\Middleware\EnforceOwnerPlatformPermissions;
use App\Http\Middleware\EnsureCurrentBusinessAgreementAccepted;
use App\Models\KycVerification;
use App\Models\Shop;
use App\Models\User;
use Inertia\Testing\AssertableInertia as Assert;

beforeEach(function () {
    $this->withoutMiddleware([
        EnsureCurrentBusinessAgreementAccepted::class,
        EnforceOwnerPlatformPermissions::class,
    ]);
});

function locationOwner(string $permitAddress): User
{
    $owner = User::factory()->create(['role' => AccountType::ShopOwner->value]);
    Shop::create([
        'owner_id' => $owner->id,
        'shop_name' => 'Sample Laundry Shop',
        'phone' => '09171234567',
        'block_street' => '12 Sample St',
        'municipality' => 'Imus',
        'barangay' => 'Sample',
        'postal_code' => '4103',
        'status' => 'active',
        'location_mode' => 'single',
        'setup_completed_at' => now(),
    ]);
    KycVerification::forceCreate([
        'user_id' => $owner->id,
        'status' => KycVerification::STATUS_APPROVED,
        'shop_name' => 'Sample Laundry Shop',
        'phone' => '09171234567',
        'block_street' => '12 Sample St',
        'municipality' => 'Imus',
        'barangay' => 'Sample',
        'postal_code' => '4103',
        'kyc_ocr_data' => ['dti' => ['registered_address' => $permitAddress]],
        'kyc_confirmed_data' => ['registered_address' => $permitAddress],
    ]);

    return $owner;
}

test('settings exposes the permit address when OCR matched the registered address', function () {
    $owner = locationOwner('12 Sample St, Brgy. Sample, Imus, Cavite');

    $this->actingAs($owner)
        ->get(route('shop.settings'))
        ->assertInertia(fn (Assert $page) => $page
            ->where('locationVerification.verified', true)
            ->where('locationVerification.matched', true)
            ->where('locationVerification.permit_address', '12 Sample St, Brgy. Sample, Imus, Cavite'));
});

test('settings does not mark the location matched when the permit address differs', function () {
    $owner = locationOwner('99 Elsewhere Road, Quezon City');

    $this->actingAs($owner)
        ->get(route('shop.settings'))
        ->assertInertia(fn (Assert $page) => $page
            ->where('locationVerification.verified', true)
            ->where('locationVerification.matched', false));
});

test('an unverified owner has no permit address', function () {
    $owner = User::factory()->create(['role' => AccountType::ShopOwner->value]);
    Shop::create([
        'owner_id' => $owner->id, 'shop_name' => 'Plain Shop', 'phone' => '09171234567',
        'municipality' => 'Imus', 'barangay' => 'Poblacion', 'postal_code' => '4103',
        'status' => 'active', 'location_mode' => 'single', 'setup_completed_at' => now(),
    ]);

    $this->actingAs($owner)
        ->get(route('shop.settings'))
        ->assertInertia(fn (Assert $page) => $page
            ->where('locationVerification.verified', false)
            ->where('locationVerification.permit_address', null));
});
