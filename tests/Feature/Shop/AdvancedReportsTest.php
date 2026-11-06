<?php

use App\Enums\AccountType;
use App\Http\Middleware\EnforceOwnerPlatformPermissions;
use App\Http\Middleware\EnsureCurrentBusinessAgreementAccepted;
use App\Models\Order;
use App\Models\Shop;
use App\Models\User;
use App\Services\ShopSetupService;

beforeEach(function () {
    $this->withoutVite();
    $this->withoutMiddleware([
        EnsureCurrentBusinessAgreementAccepted::class,
        EnforceOwnerPlatformPermissions::class,
    ]);
});

function reportShop(string $plan, bool $trial = false): Shop
{
    $owner = User::factory()->create(['role' => AccountType::ShopOwner->value]);
    $shop = Shop::create([
        'owner_id' => $owner->id,
        'shop_name' => 'Report Laundry '.$owner->id,
        'phone' => '09171234567',
        'municipality' => 'Imus',
        'barangay' => 'Poblacion',
        'postal_code' => '4103',
        'status' => 'active',
        'setup_completed_at' => now(),
    ]);

    Order::create([
        'user_id' => $owner->id,
        'shop_name' => $shop->shop_name,
        'owner_name' => $owner->name,
        'email' => $owner->email,
        'phone' => '09171234567',
        'municipality' => 'Imus',
        'barangay' => 'Poblacion',
        'postal_code' => '4103',
        'plan_name' => $plan,
        'billing_months' => 1,
        'is_trial' => $trial,
        'total_price' => $trial ? 0 : 1999,
        'status' => $trial ? 'approved' : 'paid',
        'expires_at' => now()->addMonth(),
    ]);

    return $shop;
}

test('only the Premium plan includes advanced reports', function (string $plan, bool $trial, bool $expected) {
    $shop = reportShop($plan, $trial);

    expect(app(ShopSetupService::class)->shopSupports($shop->id, 'advanced-reports'))->toBe($expected);
})->with([
    'premium' => ['Premium', false, true],
    'standard' => ['Standard', false, false],
    'basic' => ['Basic', false, false],
    'trial' => ['Standard', true, false],
]);

test('a premium shop opens every report', function (string $page) {
    $shop = reportShop('Premium');

    $this->actingAs($shop->owner)->get("/shop/reports{$page}")->assertOk();
})->with(['', '/operations', '/inventory', '/finance', '/pricing', '/employee']);

test('a standard shop keeps the basic reports', function (string $page) {
    $shop = reportShop('Standard');

    $this->actingAs($shop->owner)->get("/shop/reports{$page}")->assertOk();
})->with(['', '/operations', '/inventory']);

test('a standard shop is sent back from the advanced reports with a notice', function (string $page) {
    $shop = reportShop('Standard');

    $this->actingAs($shop->owner)
        ->get("/shop/reports{$page}")
        ->assertRedirect('/shop/reports')
        ->assertSessionHas('toast.type', 'warning');
})->with(['/finance', '/pricing', '/employee']);

test('the report menu knows whether advanced reports are included', function () {
    $this->actingAs(reportShop('Premium')->owner)->get('/shop/reports')
        ->assertInertia(fn ($page) => $page->where('shopCapabilities.advanced_reports', true));
    $this->actingAs(reportShop('Basic')->owner)->get('/shop/reports')
        ->assertInertia(fn ($page) => $page->where('shopCapabilities.advanced_reports', false));
});
