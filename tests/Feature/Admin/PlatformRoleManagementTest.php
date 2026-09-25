<?php

use App\Enums\AccountType;
use App\Models\PlatformRole;
use App\Models\User;
use App\Services\PlatformRoleService;
use Illuminate\Support\Facades\Route;
use Inertia\Testing\AssertableInertia as Assert;

test('super admin can view the platform permission matrix', function () {
    $admin = User::factory()->create(['role' => AccountType::SuperAdmin->value]);

    $this->actingAs($admin)
        ->get(route('admin.settings.roles-permissions.index'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('admin/settings/RolesPermissions')
            ->has('roles', 3)
            ->has('permissionGroups', 4)
            ->where('roles.0.slug', 'super_admin')
        );
});

test('super admin can create a custom role and toggle a permission', function () {
    $admin = User::factory()->create(['role' => AccountType::SuperAdmin->value]);

    $response = $this->actingAs($admin)
        ->postJson(route('admin.settings.roles-permissions.store'), [
            'name' => 'Support Agent',
            'description' => 'Handles platform support requests.',
        ])
        ->assertCreated()
        ->assertJsonPath('name', 'Support Agent');

    $role = PlatformRole::where('public_id', $response->json('public_id'))->firstOrFail();

    $this->actingAs($admin)
        ->patchJson(route('admin.settings.roles-permissions.permissions.toggle', $role), [
            'permission' => 'admin.issues',
        ])
        ->assertOk()
        ->assertJsonPath('enabled', true);

    $this->assertDatabaseHas('platform_role_permissions', [
        'platform_role_id' => $role->id,
        'permission' => 'admin.issues',
    ]);
});

test('super admin role permissions are locked', function () {
    $admin = User::factory()->create(['role' => AccountType::SuperAdmin->value]);
    $role = PlatformRole::where('slug', 'super_admin')->firstOrFail();

    $this->actingAs($admin)
        ->patchJson(route('admin.settings.roles-permissions.permissions.toggle', $role), [
            'permission' => 'admin.roles',
        ])
        ->assertUnprocessable();
});

test('regular users cannot manage platform roles', function () {
    $user = User::factory()->create(['role' => AccountType::Customer->value]);

    $this->actingAs($user)
        ->get(route('admin.settings.roles-permissions.index'))
        ->assertRedirect(route(AccountType::Customer->dashboardRoute()));
});

test('custom roles can be deleted but system roles cannot', function () {
    $admin = User::factory()->create(['role' => AccountType::SuperAdmin->value]);
    $customRole = PlatformRole::create([
        'name' => 'Auditor',
        'slug' => 'auditor',
        'description' => 'Reviews records.',
    ]);
    $systemRole = PlatformRole::where('slug', 'owner')->firstOrFail();

    $this->actingAs($admin)
        ->deleteJson(route('admin.settings.roles-permissions.destroy', $customRole))
        ->assertOk();
    $this->assertDatabaseMissing('platform_roles', ['id' => $customRole->id]);

    $this->actingAs($admin)
        ->deleteJson(route('admin.settings.roles-permissions.destroy', $systemRole))
        ->assertUnprocessable();
});

test('an owner with unchecked permissions cannot access shop pages or actions', function () {
    $owner = User::factory()->create(['role' => AccountType::ShopOwner->value]);
    $ownerRole = PlatformRole::where('slug', 'owner')->firstOrFail();
    $ownerRole->permissions()->delete();

    $this->actingAs($owner)
        ->get(route('shop.dashboard'))
        ->assertRedirect(route('landing'))
        ->assertSessionHas('toast.message', 'Your role does not have permission to access this feature.');

    $this->actingAs($owner)
        ->postJson(route('employee.store'), [])
        ->assertForbidden();
});

test('every owner route is mapped to a persisted platform permission', function () {
    $service = app(PlatformRoleService::class);

    $ownerRouteUris = collect(Route::getRoutes()->getRoutes())
        ->filter(fn ($route) => in_array('role:owner', $route->gatherMiddleware(), true))
        ->map(fn ($route) => $route->uri())
        ->unique();

    expect($ownerRouteUris)->not->toBeEmpty();

    $ownerRouteUris->each(
        fn (string $uri) => expect($service->canAccessOwnerPath($uri))->toBeTrue()
    );
});
