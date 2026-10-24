<?php

use App\Enums\AccountType;
use App\Models\Inventory;
use App\Models\LowStockAlert;
use App\Models\Shop;
use App\Models\User;
use App\Services\InventoryService;

function stockShop(): Shop
{
    $owner = User::factory()->create(['role' => AccountType::ShopOwner->value]);

    return Shop::create([
        'owner_id' => $owner->id,
        'shop_name' => 'Stock Laundry',
        'phone' => '09171234567',
        'municipality' => 'Imus',
        'barangay' => 'Poblacion',
        'postal_code' => '4103',
        'status' => 'active',
    ]);
}

function stockItem(Shop $shop, int $quantity, int $min): Inventory
{
    return Inventory::create([
        'shop_id' => $shop->id,
        'name' => 'Detergent',
        'sku' => 'SKU-'.uniqid(),
        'unit' => 'pcs',
        'quantity' => $quantity,
        'min_stock' => $min,
    ]);
}

test('an item that reaches its minimum raises a low stock alert', function () {
    $shop = stockShop();
    $item = stockItem($shop, 10, 5);
    $item->decrement('quantity', 5);

    app(InventoryService::class)->alertIfLowStock($item->refresh());

    $alert = LowStockAlert::where('inventory_id', $item->id)->first();
    expect($alert)->not->toBeNull()
        ->and($alert->status)->toBe('unread')
        ->and($alert->quantity_at_alert)->toBe(5);
});

test('an item above its minimum raises no alert', function () {
    $item = stockItem(stockShop(), 10, 5);

    app(InventoryService::class)->alertIfLowStock($item);

    expect(LowStockAlert::count())->toBe(0);
});

test('raising the minimum above the stock raises an alert and repeats do not duplicate it', function () {
    $item = stockItem(stockShop(), 8, 5);
    $service = app(InventoryService::class);

    $service->update($item, ['min_stock' => 10]);
    $service->update($item, ['min_stock' => 12]);

    expect(LowStockAlert::where('inventory_id', $item->id)->where('status', 'unread')->count())->toBe(1);
});

test('the backfill alerts only low items that never had an alert', function () {
    $shop = stockShop();
    $low = stockItem($shop, 5, 5);
    $healthy = stockItem($shop, 50, 5);
    $dismissed = stockItem($shop, 1, 5);
    LowStockAlert::create([
        'inventory_id' => $dismissed->id,
        'shop_id' => $shop->id,
        'quantity_at_alert' => 1,
        'min_stock_at_alert' => 5,
        'status' => 'dismissed',
    ]);

    $this->artisan('inventory:backfill-low-stock-alerts')->expectsOutput('Created 1 low stock alert(s).')->assertSuccessful();
    $this->artisan('inventory:backfill-low-stock-alerts')->expectsOutput('Created 0 low stock alert(s).')->assertSuccessful();

    expect(LowStockAlert::where('inventory_id', $low->id)->where('status', 'unread')->count())->toBe(1)
        ->and(LowStockAlert::where('inventory_id', $healthy->id)->count())->toBe(0)
        ->and(LowStockAlert::where('inventory_id', $dismissed->id)->count())->toBe(1);
});
