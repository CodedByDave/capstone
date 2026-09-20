<?php

use App\Enums\AccountType;
use App\Models\User;
use Inertia\Testing\AssertableInertia as Assert;

test('a super admin can view a user details page', function () {
    $admin = User::factory()->create([
        'role' => AccountType::SuperAdmin->value,
    ]);
    $user = User::factory()->create([
        'role' => AccountType::Customer->value,
    ]);

    $this->actingAs($admin)
        ->get(route('admin.users.show', ['user' => $user->public_id]))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('admin/users/Show')
            ->where('user.id', $user->id)
            ->where('user.name', $user->name)
            ->where('user.email', $user->email)
        );
});

test('a non-admin is redirected away from the admin user details page', function () {
    $viewer = User::factory()->create([
        'role' => AccountType::Customer->value,
    ]);
    $user = User::factory()->create();

    $this->actingAs($viewer)
        ->get(route('admin.users.show', ['user' => $user->public_id]))
        ->assertRedirect(route('user.dashboard'));
});

test('the numeric database id is not accepted by the user details route', function () {
    $admin = User::factory()->create([
        'role' => AccountType::SuperAdmin->value,
    ]);
    $user = User::factory()->create();

    $this->actingAs($admin)
        ->get("/admin/users/{$user->id}")
        ->assertNotFound();
});
