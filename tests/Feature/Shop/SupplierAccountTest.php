<?php

use App\Enums\AccountType;
use App\Http\Middleware\EnforceOwnerPlatformPermissions;
use App\Http\Middleware\EnsureCurrentBusinessAgreementAccepted;
use App\Mail\SupplierInvitationMail;
use App\Models\Employee;
use App\Models\EmployeeRole;
use App\Models\Order;
use App\Models\RolePermission;
use App\Models\Shop;
use App\Models\Supplier;
use App\Models\User;
use Illuminate\Support\Facades\Mail;
use Inertia\Testing\AssertableInertia as Assert;

beforeEach(function () {
    $this->withoutMiddleware([
        EnsureCurrentBusinessAgreementAccepted::class,
        EnforceOwnerPlatformPermissions::class,
    ]);
    Mail::fake();
});

/** An owner with a set-up, subscribed shop; suppliers are on unless told otherwise. */
function supplierTestOwner(string $shopName = 'Supply Laundry', bool $hasSuppliers = true): User
{
    $owner = User::factory()->create(['role' => AccountType::ShopOwner->value]);
    Shop::create([
        'owner_id' => $owner->id,
        'shop_name' => $shopName,
        'phone' => '09171234567',
        'municipality' => 'Imus',
        'barangay' => 'Poblacion',
        'postal_code' => '4103',
        'status' => 'active',
        'setup_completed_at' => now(),
        'has_suppliers' => $hasSuppliers,
    ]);
    Order::create([
        'user_id' => $owner->id,
        'shop_name' => $shopName,
        'owner_name' => $owner->name,
        'email' => $owner->email,
        'phone' => '09171234567',
        'municipality' => 'Imus',
        'barangay' => 'Poblacion',
        'postal_code' => '4103',
        'plan_name' => 'Premium',
        'billing_months' => 1,
        'is_trial' => false,
        'total_price' => 2239,
        'status' => 'paid',
        'expires_at' => now()->addMonth(),
    ]);

    return $owner->fresh();
}

function supplierTestShop(User $owner): Shop
{
    return Shop::where('owner_id', $owner->id)->firstOrFail();
}

function supplierTestPayload(array $overrides = []): array
{
    return array_merge([
        'name' => 'Clean Supply Co.',
        'contact_person' => 'Ana Reyes',
        'email' => 'ana@cleansupply.test',
        'lead_time_days' => 3,
        'status' => 'active',
    ], $overrides);
}

/** A staff login of the shop holding the given Inventory Management actions. */
function supplierTestStaff(Shop $shop, array $actions): User
{
    $user = User::factory()->create(['role' => AccountType::Staff->value]);
    $employee = Employee::create([
        'shop_id' => $shop->id,
        'user_id' => $user->id,
        'employee_id' => 'INV-'.$user->id,
        'first_name' => 'Inv',
        'last_name' => 'Clerk',
        'position' => 'Staff',
        'hire_date' => today(),
        'status' => 'Active',
    ]);
    EmployeeRole::create(['employee_id' => $employee->id, 'role' => 'stockkeeper']);

    foreach ($actions as $action) {
        RolePermission::create([
            'shop_id' => $shop->id,
            'role' => 'stockkeeper',
            'module' => 'Inventory Management',
            'action' => $action,
        ]);
    }

    return $user;
}

// ── Operations Setup flag ──────────────────────────────────────────────────

test('operations setup saves whether the shop buys from suppliers', function () {
    $owner = supplierTestOwner(hasSuppliers: false);

    $this->actingAs($owner)->put('/shop/setup', [
        'offers_pickup' => false,
        'offers_delivery' => false,
        'has_suppliers' => true,
        'ownership_type' => Shop::OWNERSHIP_INDEPENDENT,
    ])->assertRedirect();

    expect(supplierTestShop($owner)->has_suppliers)->toBeTrue();

    $this->actingAs($owner)->get('/shop/setup')->assertInertia(fn (Assert $page) => $page
        ->component('shop/setup/Index')
        ->where('shop.has_suppliers', true));
});

test('the shop capabilities shared with pages include the supplier flag', function () {
    $owner = supplierTestOwner(hasSuppliers: false);

    $this->actingAs($owner)->get('/shop/inventory')->assertInertia(fn (Assert $page) => $page
        ->where('shopCapabilities.has_suppliers', false));
});

test('supplier pages are blocked while the shop has suppliers turned off', function () {
    $owner = supplierTestOwner(hasSuppliers: false);

    $this->actingAs($owner)->get('/shop/supplier')->assertForbidden();
    $this->actingAs($owner)->post('/shop/supplier', supplierTestPayload())->assertForbidden();

    expect(Supplier::count())->toBe(0);
});

// ── Supplier account creation ──────────────────────────────────────────────

test('adding a supplier creates their login and emails a set-password invitation', function () {
    $owner = supplierTestOwner();

    $this->actingAs($owner)
        ->post('/shop/supplier', supplierTestPayload())
        ->assertRedirect(route('supplier.index'));

    $supplier = Supplier::firstOrFail();
    $login = User::findOrFail($supplier->user_id);

    expect($supplier->shop_id)->toBe(supplierTestShop($owner)->id)
        ->and($supplier->public_id)->not->toBeEmpty()
        ->and($login->role)->toBe(AccountType::Supplier->value)
        ->and($login->email)->toBe('ana@cleansupply.test')
        ->and($login->name)->toBe('Ana Reyes');

    Mail::assertSent(SupplierInvitationMail::class, fn (SupplierInvitationMail $mail) => $mail->hasTo('ana@cleansupply.test')
        && str_contains($mail->setPasswordUrl, '/reset-password/')
        && $mail->shopName === 'Supply Laundry');
});

test('a supplier needs an email address that no other account uses', function () {
    $owner = supplierTestOwner();
    User::factory()->create(['email' => 'taken@example.test']);

    $this->actingAs($owner)
        ->post('/shop/supplier', supplierTestPayload(['email' => '']))
        ->assertSessionHasErrors('email');
    $this->actingAs($owner)
        ->post('/shop/supplier', supplierTestPayload(['email' => 'taken@example.test']))
        ->assertSessionHasErrors('email');

    expect(Supplier::count())->toBe(0);
    Mail::assertNothingSent();
});

test('changing a supplier email also changes their login email', function () {
    $owner = supplierTestOwner();
    $this->actingAs($owner)->post('/shop/supplier', supplierTestPayload());
    $supplier = Supplier::firstOrFail();

    $this->actingAs($owner)
        ->put("/shop/supplier/{$supplier->public_id}", supplierTestPayload(['email' => 'new@cleansupply.test']))
        ->assertRedirect(route('supplier.index'));

    expect($supplier->user->fresh()->email)->toBe('new@cleansupply.test');
});

test('a supplier added before supplier logins existed can be sent an invite', function () {
    $owner = supplierTestOwner();
    $supplier = Supplier::create([
        'shop_id' => supplierTestShop($owner)->id,
        'name' => 'Old Supplier',
        'email' => 'old@supplier.test',
        'lead_time_days' => 2,
        'status' => 'active',
    ]);

    $this->actingAs($owner)
        ->postJson("/shop/supplier/{$supplier->public_id}/invite")
        ->assertOk();

    expect($supplier->fresh()->user_id)->not->toBeNull();
    Mail::assertSent(SupplierInvitationMail::class, fn ($mail) => $mail->hasTo('old@supplier.test'));
});

test('an invite needs the supplier to have an email address', function () {
    $owner = supplierTestOwner();
    $supplier = Supplier::create([
        'shop_id' => supplierTestShop($owner)->id,
        'name' => 'No Email Supplier',
        'lead_time_days' => 2,
        'status' => 'active',
    ]);

    $this->actingAs($owner)
        ->postJson("/shop/supplier/{$supplier->public_id}/invite")
        ->assertStatus(422);

    expect($supplier->fresh()->user_id)->toBeNull();
});

// ── Tenant isolation ───────────────────────────────────────────────────────

test('a shop cannot see or change another shop\'s supplier', function () {
    $ownerA = supplierTestOwner('Shop A');
    $ownerB = supplierTestOwner('Shop B');
    $this->actingAs($ownerB)->post('/shop/supplier', supplierTestPayload(['email' => 'b@supplier.test']));
    $supplierB = Supplier::firstOrFail();

    $this->actingAs($ownerA)->get("/shop/supplier/{$supplierB->public_id}")->assertNotFound();
    $this->actingAs($ownerA)->get("/shop/supplier/{$supplierB->public_id}/edit")->assertNotFound();
    $this->actingAs($ownerA)
        ->put("/shop/supplier/{$supplierB->public_id}", supplierTestPayload(['name' => 'Hijacked']))
        ->assertNotFound();
    $this->actingAs($ownerA)->postJson("/shop/supplier/{$supplierB->public_id}/invite")->assertNotFound();
    $this->actingAs($ownerA)->deleteJson("/shop/supplier/{$supplierB->public_id}")->assertNotFound();

    $supplierB->delete();
    $this->actingAs($ownerA)->postJson("/shop/supplier/{$supplierB->public_id}/restore")->assertNotFound();

    expect(Supplier::withTrashed()->find($supplierB->id))
        ->name->toBe('Clean Supply Co.')
        ->deleted_at->not->toBeNull();
});

test('a supplier is no longer reachable by its sequential id', function () {
    $owner = supplierTestOwner();
    $this->actingAs($owner)->post('/shop/supplier', supplierTestPayload());
    $supplier = Supplier::firstOrFail();

    $this->actingAs($owner)->get("/shop/supplier/{$supplier->id}/edit")->assertNotFound();
});

test('the supplier list only shows the shop\'s own suppliers', function () {
    $ownerA = supplierTestOwner('Shop A');
    $ownerB = supplierTestOwner('Shop B');
    $this->actingAs($ownerA)->post('/shop/supplier', supplierTestPayload(['name' => 'Mine', 'email' => 'a@s.test']));
    $this->actingAs($ownerB)->post('/shop/supplier', supplierTestPayload(['name' => 'Theirs', 'email' => 'b@s.test']));

    $this->actingAs($ownerA)->get('/shop/supplier')->assertInertia(fn (Assert $page) => $page
        ->component('shop/inventory/supplier/Index')
        ->has('suppliers.data', 1)
        ->where('suppliers.data.0.name', 'Mine'));
});

// ── Staff ──────────────────────────────────────────────────────────────────

test('staff with inventory view can open a supplier but not invite without update', function () {
    $owner = supplierTestOwner();
    $shop = supplierTestShop($owner);
    $this->actingAs($owner)->post('/shop/supplier', supplierTestPayload());
    $supplier = Supplier::firstOrFail();
    $viewer = supplierTestStaff($shop, ['view']);

    $this->actingAs($viewer)->get("/staff/supplier/{$supplier->public_id}")
        ->assertInertia(fn (Assert $page) => $page
            ->component('shop/inventory/supplier/Show')
            ->where('supplier.public_id', $supplier->public_id));

    $this->actingAs($viewer)->post("/staff/supplier/{$supplier->public_id}/invite")
        ->assertRedirect(route('staff.dashboard'));
});

test('bulk supplier archive is blocked while suppliers are off', function () {
    $owner = supplierTestOwner(hasSuppliers: false);
    $supplier = Supplier::create(['shop_id' => supplierTestShop($owner)->id, 'name' => 'Hidden', 'status' => 'active']);

    $this->actingAs($owner)->post('/shop/bulk/supplier/archive', ['ids' => [$supplier->public_id]])->assertForbidden();

    expect($supplier->fresh()->trashed())->toBeFalse();
});

test('a supplier login that was archived is restored when invited again', function () {
    $owner = supplierTestOwner();
    $this->actingAs($owner)->post('/shop/supplier', supplierTestPayload());
    $supplier = Supplier::firstOrFail();
    $supplier->user->delete();

    $this->actingAs($owner)->postJson("/shop/supplier/{$supplier->public_id}/invite")->assertOk();

    expect(User::find($supplier->user_id))->not->toBeNull();
    Mail::assertSent(SupplierInvitationMail::class, 2);
});
