<?php

use App\Enums\AccountType;
use App\Models\Expense;
use App\Models\Inventory;
use App\Models\Shop;
use App\Models\User;
use App\Services\InventoryService;

function restockItem(float $unitPrice): Inventory
{
    $owner = User::factory()->create(['role' => AccountType::ShopOwner->value]);
    $shop = Shop::create([
        'owner_id' => $owner->id,
        'shop_name' => 'Restock Laundry',
        'phone' => '09171234567',
        'municipality' => 'Imus',
        'barangay' => 'Poblacion',
        'postal_code' => '4103',
        'status' => 'active',
    ]);

    return Inventory::create([
        'shop_id' => $shop->id,
        'name' => 'Detergent',
        'sku' => 'SKU-'.uniqid(),
        'unit' => 'pcs',
        'quantity' => 2,
        'min_stock' => 1,
        'unit_price' => $unitPrice,
    ]);
}

test('restocking an item records its cost as a finance expense', function () {
    $item = restockItem(25.50);

    app(InventoryService::class)->adjustStock($item, 'restock', 10, 'PO-1');

    $expense = Expense::where('shop_id', $item->shop_id)->first();
    expect($expense)->not->toBeNull()
        ->and((float) $expense->amount)->toBe(255.0)
        ->and($expense->title)->toBe('Restock: Detergent')
        ->and($expense->reference_number)->toBe('PO-1')
        ->and($expense->category->name)->toBe('Inventory Restock');
});

test('other stock adjustments and free items record no expense', function () {
    $item = restockItem(25.50);
    $free = restockItem(0);

    app(InventoryService::class)->adjustStock($item, 'usage', -1);
    app(InventoryService::class)->adjustStock($free, 'restock', 5);

    expect(Expense::count())->toBe(0);
});
