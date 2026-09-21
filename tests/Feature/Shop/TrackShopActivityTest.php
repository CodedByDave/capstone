<?php

use App\Enums\AccountType;
use App\Http\Middleware\TrackShopActivity;
use App\Models\Employee;
use App\Models\Shop;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Symfony\Component\HttpFoundation\Response;

function shopForActivityTracking(User $owner, array $attributes = []): Shop
{
    return Shop::create(array_merge([
        'owner_id' => $owner->id,
        'shop_name' => 'Activity Test Laundry',
        'phone' => '09171234567',
        'municipality' => 'Manila',
        'barangay' => 'Test Barangay',
        'postal_code' => '1000',
        'status' => 'active',
    ], $attributes));
}

test('successful owner mutations record shop activity', function () {
    $this->travelTo(now()->startOfSecond());
    $owner = User::factory()->create(['role' => AccountType::ShopOwner->value]);
    $shop = shopForActivityTracking($owner, [
        'last_activity_at' => now()->subDays(40),
    ]);
    $request = Request::create('/shop/employee', 'POST');
    $request->setUserResolver(fn () => $owner);

    app(TrackShopActivity::class)->handle(
        $request,
        fn () => new Response('', Response::HTTP_FOUND),
    );

    expect($shop->fresh()->last_activity_at->equalTo(now()))->toBeTrue();
});

test('staff activity updates the employee shop', function () {
    $this->travelTo(now()->startOfSecond());
    $owner = User::factory()->create(['role' => AccountType::ShopOwner->value]);
    $staff = User::factory()->create(['role' => AccountType::Staff->value]);
    $shop = shopForActivityTracking($owner);
    Employee::create([
        'user_id' => $staff->id,
        'shop_id' => $shop->id,
        'employee_id' => 'EMP-TRACK-001',
        'first_name' => 'Activity',
        'last_name' => 'Tracker',
        'position' => 'Manager',
        'hire_date' => today(),
    ]);
    $request = Request::create('/staff/dashboard', 'GET');
    $request->setUserResolver(fn () => $staff);

    app(TrackShopActivity::class)->handle(
        $request,
        fn () => new Response('', Response::HTTP_OK),
    );

    expect($shop->fresh()->last_activity_at->equalTo(now()))->toBeTrue();
});

test('shop and staff routes use shop activity tracking', function () {
    expect(Route::getRoutes()->getByName('employee.store')->gatherMiddleware())
        ->toContain('shop.activity')
        ->and(Route::getRoutes()->getByName('staff.dashboard')->gatherMiddleware())
        ->toContain('shop.activity');
});
