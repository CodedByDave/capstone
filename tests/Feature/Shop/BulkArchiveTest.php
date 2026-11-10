<?php

use App\Enums\AccountType;
use App\Http\Middleware\EnforceOwnerPlatformPermissions;
use App\Http\Middleware\EnsureCurrentBusinessAgreementAccepted;
use App\Models\Inventory;
use App\Models\Order;
use App\Models\Shop;
use App\Models\Supplier;
use App\Models\User;

beforeEach(function () {
    $this->withoutMiddleware([
        EnsureCurrentBusinessAgreementAccepted::class,
        EnforceOwnerPlatformPermissions::class,
    ]);
});

function bulkShop(): array
{
    $owner = User::factory()->create(['role' => AccountType::ShopOwner->value]);
    $shop = Shop::create([
        'owner_id' => $owner->id,
        'shop_name' => 'Bulk Laundry '.$owner->id,
        'phone' => '09171234567',
        'municipality' => 'Imus',
        'barangay' => 'Poblacion',
        'postal_code' => '4103',
        'status' => 'active',
        'setup_completed_at' => now(),
        'has_suppliers' => true,
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
        'plan_name' => 'Premium',
        'billing_months' => 1,
        'is_trial' => false,
        'total_price' => 2239,
        'status' => 'paid',
        'expires_at' => now()->addMonth(),
    ]);

    return [$owner->fresh(), $shop];
}

it('archives every selected supplier and restores them again', function () {
    [$owner, $shop] = bulkShop();
    $suppliers = collect(['A', 'B', 'C'])->map(fn ($n) => Supplier::create([
        'shop_id' => $shop->id, 'name' => "Supplier {$n}", 'status' => 'active',
    ]));
    // Supplier rows are selected by their public id, never the sequential id.
    $picked = $suppliers->take(2)->pluck('public_id')->all();

    $this->actingAs($owner)
        ->post('/shop/bulk/supplier/archive', ['ids' => $picked])
        ->assertRedirect();

    expect(Supplier::onlyTrashed()->pluck('public_id')->sort()->values()->all())->toBe(collect($picked)->sort()->values()->all())
        ->and(Supplier::count())->toBe(1);

    $this->actingAs($owner)
        ->post('/shop/bulk/supplier/restore', ['ids' => $picked])
        ->assertRedirect();

    expect(Supplier::onlyTrashed()->count())->toBe(0)->and(Supplier::count())->toBe(3);
});

it('archives selected inventory items', function () {
    [$owner, $shop] = bulkShop();
    $items = collect(['Bleach', 'Downy'])->map(fn ($name) => Inventory::create([
        'shop_id' => $shop->id, 'name' => $name, 'unit' => 'pcs', 'quantity' => 5,
        'min_stock' => 1, 'unit_price' => 5, 'status' => 'active',
    ]));

    $this->actingAs($owner)
        ->post('/shop/bulk/inventory/archive', ['ids' => $items->pluck('id')->all()])
        ->assertRedirect();

    expect(Inventory::count())->toBe(0)->and(Inventory::onlyTrashed()->count())->toBe(2);
});

it('never touches another shop\'s records', function () {
    [$owner] = bulkShop();
    [, $otherShop] = bulkShop();
    $foreign = Supplier::create(['shop_id' => $otherShop->id, 'name' => 'Foreign', 'status' => 'active']);

    $this->actingAs($owner)
        ->post('/shop/bulk/supplier/archive', ['ids' => [$foreign->public_id]])
        ->assertRedirect();

    expect(Supplier::find($foreign->id))->not->toBeNull();
});

it('rejects an empty or malformed selection', function () {
    [$owner] = bulkShop();

    $this->actingAs($owner)->post('/shop/bulk/supplier/archive', ['ids' => []])->assertSessionHasErrors('ids');
    $this->actingAs($owner)->post('/shop/bulk/supplier/archive', ['ids' => ['1; drop']])->assertSessionHasErrors('ids.0');
});
