<?php

use App\Enums\AccountType;

test('each account type has the correct dashboard route', function (
    AccountType $accountType,
    string $routeName,
) {
    expect($accountType->dashboardRoute())->toBe($routeName);
})->with([
    'customer' => [AccountType::Customer, 'user.dashboard'],
    'shop owner' => [AccountType::ShopOwner, 'shop.dashboard'],
    'staff' => [AccountType::Staff, 'staff.dashboard'],
    'super admin' => [AccountType::SuperAdmin, 'admin.dashboard'],
]);

test('unknown account types fall back to the landing page', function () {
    expect(AccountType::dashboardRouteFor('manager'))->toBe('landing')
        ->and(AccountType::dashboardRouteFor(null))->toBe('landing');
});
