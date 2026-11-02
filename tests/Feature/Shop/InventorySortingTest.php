<?php

use App\Enums\AccountType;
use App\Http\Middleware\EnforceOwnerPlatformPermissions;
use App\Http\Middleware\EnsureCurrentBusinessAgreementAccepted;
use App\Models\Inventory;
use App\Models\InventoryCategory;
use App\Models\Order;
use App\Models\Shop;
use App\Models\Supplier;
use App\Models\User;
use App\Repositories\InventoryRepository;
use Inertia\Testing\AssertableInertia as Assert;

beforeEach(function () {
    $this->withoutMiddleware([
        EnsureCurrentBusinessAgreementAccepted::class,
        EnforceOwnerPlatformPermissions::class,
    ]);
});

function sortingShop(string $name = 'Sorting Laundry'): array
{
    $owner = User::factory()->create(['role' => AccountType::ShopOwner->value]);
    $shop = Shop::create([
        'owner_id' => $owner->id,
        'shop_name' => $name.' '.$owner->id,
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
        'plan_name' => 'Premium',
        'billing_months' => 1,
        'is_trial' => false,
        'total_price' => 2239,
        'status' => 'paid',
        'expires_at' => now()->addMonth(),
    ]);

    return [$owner->fresh(), $shop];
}

function sortingItem(Shop $shop, string $name, array $attributes = []): Inventory
{
    return Inventory::create(array_merge([
        'shop_id' => $shop->id,
        'name' => $name,
        'unit' => 'pcs',
        'quantity' => 10,
        'min_stock' => 2,
        'unit_price' => 5,
        'status' => 'active',
    ], $attributes));
}

/** Three items whose order differs by every sortable column. */
function sortingCatalog(Shop $shop): void
{
    $detergent = InventoryCategory::create(['shop_id' => $shop->id, 'name' => 'Detergent']);
    $accessory = InventoryCategory::create(['shop_id' => $shop->id, 'name' => 'Accessory']);
    $abc = Supplier::create(['shop_id' => $shop->id, 'name' => 'Abc Trading', 'status' => 'active']);
    $zed = Supplier::create(['shop_id' => $shop->id, 'name' => 'Zed Supply', 'status' => 'active']);

    sortingItem($shop, 'Bleach', ['quantity' => 30, 'unit_price' => 45, 'inventory_categories_id' => $detergent->id, 'supplier_id' => $zed->id, 'status' => 'active']);
    sortingItem($shop, 'Hangers', ['quantity' => 5, 'unit_price' => 7, 'inventory_categories_id' => $accessory->id, 'supplier_id' => $abc->id, 'status' => 'inactive']);
    sortingItem($shop, 'Downy', ['quantity' => 20, 'unit_price' => 20, 'status' => 'active']);
}

function sortedNames(Shop $shop, string $by, string $direction): array
{
    return app(InventoryRepository::class)
        ->paginateForShop($shop->id, 15, ['sort_by' => $by, 'sort_direction' => $direction])
        ->pluck('name')
        ->all();
}

it('sorts by item name, A to Z by default', function () {
    [, $shop] = sortingShop();
    sortingCatalog($shop);

    expect(app(InventoryRepository::class)->paginateForShop($shop->id)->pluck('name')->all())
        ->toBe(['Bleach', 'Downy', 'Hangers'])
        ->and(sortedNames($shop, 'name', 'desc'))->toBe(['Hangers', 'Downy', 'Bleach']);
});

it('sorts by on hand quantity and by unit price', function () {
    [, $shop] = sortingShop();
    sortingCatalog($shop);

    expect(sortedNames($shop, 'quantity', 'asc'))->toBe(['Hangers', 'Downy', 'Bleach'])
        ->and(sortedNames($shop, 'quantity', 'desc'))->toBe(['Bleach', 'Downy', 'Hangers'])
        ->and(sortedNames($shop, 'unit_price', 'asc'))->toBe(['Hangers', 'Downy', 'Bleach'])
        ->and(sortedNames($shop, 'unit_price', 'desc'))->toBe(['Bleach', 'Downy', 'Hangers']);
});

it('sorts by category and supplier name, with items that have none first', function () {
    [, $shop] = sortingShop();
    sortingCatalog($shop);

    // Downy has neither a category nor a supplier.
    expect(sortedNames($shop, 'category', 'asc'))->toBe(['Downy', 'Hangers', 'Bleach'])
        ->and(sortedNames($shop, 'category', 'desc'))->toBe(['Bleach', 'Hangers', 'Downy'])
        ->and(sortedNames($shop, 'supplier', 'asc'))->toBe(['Downy', 'Hangers', 'Bleach'])
        ->and(sortedNames($shop, 'supplier', 'desc'))->toBe(['Bleach', 'Hangers', 'Downy']);
});

it('sorts by status', function () {
    [, $shop] = sortingShop();
    sortingCatalog($shop);

    expect(sortedNames($shop, 'status', 'asc'))->toBe(['Bleach', 'Downy', 'Hangers'])
        ->and(sortedNames($shop, 'status', 'desc'))->toBe(['Hangers', 'Bleach', 'Downy']);
});

it('keeps equal rows in a stable order and sorts after filtering', function () {
    [, $shop] = sortingShop();
    sortingItem($shop, 'Beta', ['unit_price' => 5]);
    sortingItem($shop, 'Alpha', ['unit_price' => 5]);
    sortingItem($shop, 'Gamma', ['unit_price' => 9, 'status' => 'inactive']);

    $repository = app(InventoryRepository::class);

    expect(sortedNames($shop, 'unit_price', 'asc'))->toBe(['Alpha', 'Beta', 'Gamma'])
        ->and($repository->paginateForShop($shop->id, 15, [
            'status' => 'active',
            'sort_by' => 'unit_price',
            'sort_direction' => 'desc',
        ])->pluck('name')->all())->toBe(['Alpha', 'Beta']);
});

it('never sorts across shops', function () {
    [, $shop] = sortingShop();
    [, $other] = sortingShop('Other Laundry');
    sortingItem($shop, 'Mine');
    sortingItem($other, 'Theirs');

    expect(sortedNames($shop, 'name', 'asc'))->toBe(['Mine']);
});

it('sorts the inventory page through the endpoint and echoes the sort back', function () {
    [$owner, $shop] = sortingShop();
    sortingCatalog($shop);

    $this->actingAs($owner)
        ->get('/shop/inventory?sort_by=unit_price&sort_direction=desc')
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('shop/inventory/Index')
            ->where('inventory.data.0.name', 'Bleach')
            ->where('inventory.data.2.name', 'Hangers')
            ->where('filters.sort_by', 'unit_price')
            ->where('filters.sort_direction', 'desc'));

    $this->actingAs($owner)
        ->get('/shop/inventory')
        ->assertInertia(fn (Assert $page) => $page
            ->where('filters.sort_by', 'name')
            ->where('filters.sort_direction', 'asc')
            ->where('inventory.data.0.name', 'Bleach'));
});

it('rejects a sort column that is not one of the table columns', function () {
    [$owner] = sortingShop();

    $this->actingAs($owner)
        ->from('/shop/inventory')
        ->get('/shop/inventory?sort_by=shop_id;drop')
        ->assertSessionHasErrors('sort_by');

    $this->actingAs($owner)
        ->from('/shop/inventory')
        ->get('/shop/inventory?sort_by=name&sort_direction=sideways')
        ->assertSessionHasErrors('sort_direction');
});
