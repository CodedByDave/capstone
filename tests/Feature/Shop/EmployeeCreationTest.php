<?php

use App\Http\Middleware\EnforceOwnerPlatformPermissions;
use App\Http\Middleware\EnsureCurrentBusinessAgreementAccepted;
use App\Models\Employee;
use Illuminate\Support\Facades\Mail;

beforeEach(function () {
    $this->withoutMiddleware([
        EnsureCurrentBusinessAgreementAccepted::class,
        EnforceOwnerPlatformPermissions::class,
    ]);
    Mail::fake();
});

function cashierPayload(array $overrides = []): array
{
    return array_merge([
        'employee_id' => '2026-'.random_int(10000, 99999),
        'first_name' => 'Carla',
        'last_name' => 'Mercado',
        'email' => 'carla'.random_int(1000, 9999).'@example.com',
        'phone' => '09171234567',
        'address' => 'Poblacion, Imus, Cavite',
        'position' => 'cashier',
        'employment_type' => 'full_time',
        'pay_rate' => 500,
        'pay_basis' => 'daily',
        'hire_date' => today()->toDateString(),
    ], $overrides);
}

test('a new employee is always created active, whatever status the request sends', function () {
    $shop = driverShop();

    $this->actingAs($shop->owner)
        ->post('/shop/employee', cashierPayload(['status' => 'Inactive']))
        ->assertSessionHasNoErrors();

    expect(Employee::where('shop_id', $shop->id)->sole()->status)->toBe('Active');
});

test('an email used by another shop gets the same neutral message as one used in this shop', function () {
    $shop = driverShop();
    $otherShop = driverShop();
    $message = 'This email address cannot be used. Please enter a different one.';

    // An account that belongs to a different shop.
    $this->actingAs($shop->owner)
        ->post('/shop/employee', cashierPayload(['email' => $otherShop->owner->email]))
        ->assertSessionHasErrors(['email' => $message]);

    // An account that belongs to this shop.
    $this->actingAs($shop->owner)
        ->post('/shop/employee', cashierPayload(['email' => $shop->owner->email]))
        ->assertSessionHasErrors(['email' => $message]);

    expect(Employee::where('shop_id', $shop->id)->count())->toBe(0);
});
