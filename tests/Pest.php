<?php

/*
|--------------------------------------------------------------------------
| Test Case
|--------------------------------------------------------------------------
|
| The closure you provide to your test functions is always bound to a specific PHPUnit test
| case class. By default, that class is "PHPUnit\Framework\TestCase". Of course, you may
| need to change it using the "pest()" function to bind a different classes or traits.
|
*/

pest()->extend(Tests\TestCase::class)
    ->use(Illuminate\Foundation\Testing\RefreshDatabase::class)
    ->in('Feature');

/*
|--------------------------------------------------------------------------
| Expectations
|--------------------------------------------------------------------------
|
| When you're writing tests, you often need to check that values meet certain conditions. The
| "expect()" function gives you access to a set of "expectations" methods that you can use
| to assert different things. Of course, you may extend the Expectation API at any time.
|
*/

expect()->extend('toBeOne', function () {
    return $this->toBe(1);
});

/*
|--------------------------------------------------------------------------
| Functions
|--------------------------------------------------------------------------
|
| While Pest is very powerful out-of-the-box, you may have some testing code specific to your
| project that you don't want to repeat in every file. Here you can also expose helpers as
| global functions to help you to reduce the number of lines of code in your test files.
|
*/

function something()
{
    // ..
}

/**
 * A driver of the shop: an employee whose position is Driver, linked to a rider. Online and free
 * since the given time unless told otherwise, so the shop can give them waiting orders.
 */
function onlineRider(App\Models\Shop $shop, string $name = 'Rico', bool $online = true, ?Carbon\Carbon $freeSince = null): App\Models\Rider
{
    $user = App\Models\User::factory()->create(['role' => App\Enums\AccountType::Staff->value, 'shop_id' => $shop->id]);

    $employee = App\Models\Employee::create([
        'shop_id' => $shop->id,
        'user_id' => $user->id,
        'employee_id' => 'DRV-'.Illuminate\Support\Str::random(8),
        'first_name' => $name,
        'last_name' => 'Driver',
        'position' => 'Driver',
        'hire_date' => today(),
        'status' => 'Active',
    ]);

    $rider = app(App\Services\DriverRosterService::class)->sync($employee);
    $rider->update(['is_online' => $online, 'available_since' => $online ? ($freeSince ?? now()) : null]);

    return $rider->fresh();
}
