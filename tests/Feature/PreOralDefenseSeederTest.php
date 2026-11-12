<?php

use Database\Seeders\DefenseDemoSeeder;
use Database\Seeders\ModuleSeeder;
use Database\Seeders\PreOralDefenseSeeder;

beforeEach(function () {
    $this->seed(ModuleSeeder::class);
    $this->seed(PreOralDefenseSeeder::class);
});

test('every pre-oral defense account signs in and opens its own home page', function (string $email, string $password, string $home) {
    $this->post('/login', ['email' => $email, 'password' => $password])->assertRedirect();
    $this->assertAuthenticated();

    $this->get($home)->assertOk();
})->with([
    'super admin' => [PreOralDefenseSeeder::ADMIN_EMAIL, PreOralDefenseSeeder::ADMIN_PASSWORD, '/admin/dashboard'],
    'shop owner' => [DefenseDemoSeeder::OWNER_EMAIL, DefenseDemoSeeder::OWNER_PASSWORD, '/shop/dashboard'],
    'cashier' => [DefenseDemoSeeder::CASHIER_EMAIL, DefenseDemoSeeder::CASHIER_PASSWORD, '/staff/dashboard'],
    'attendant' => [DefenseDemoSeeder::ATTENDANT_EMAIL, DefenseDemoSeeder::ATTENDANT_PASSWORD, '/staff/dashboard'],
    'rider' => [DefenseDemoSeeder::RIDER_EMAIL, DefenseDemoSeeder::RIDER_PASSWORD, '/driver'],
    'customer' => [DefenseDemoSeeder::CUSTOMER_EMAIL, DefenseDemoSeeder::CUSTOMER_PASSWORD, '/user/dashboard'],
    'supplier' => [PreOralDefenseSeeder::SUPPLIER_EMAIL, PreOralDefenseSeeder::SUPPLIER_PASSWORD, '/supplier-portal'],
]);

test('running it again restores a password changed during rehearsal', function () {
    \App\Models\User::where('email', DefenseDemoSeeder::OWNER_EMAIL)->update(['password' => bcrypt('changed-by-panel')]);

    $this->seed(PreOralDefenseSeeder::class);

    $this->post('/login', ['email' => DefenseDemoSeeder::OWNER_EMAIL, 'password' => DefenseDemoSeeder::OWNER_PASSWORD]);
    $this->assertAuthenticated();
});
