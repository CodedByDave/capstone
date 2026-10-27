<?php

use App\Enums\AccountType;
use App\Models\Expense;
use App\Models\Inventory;
use App\Models\InventoryMovement;
use App\Models\Shop;
use App\Models\ShopOrder;
use App\Models\ShopService;
use App\Models\User;
use App\Services\CustomerShopCatalogService;
use App\Services\ServiceCategoryService;
use App\Services\ServiceSupplyUsageService;
use App\Services\ShopServiceService;
use Illuminate\Validation\ValidationException;

function svcShop(): Shop
{
    $owner = User::factory()->create(['role' => AccountType::ShopOwner->value]);

    return Shop::create([
        'owner_id' => $owner->id,
        'shop_name' => 'Supplies Laundry '.$owner->id,
        'phone' => '09171234567',
        'municipality' => 'Imus',
        'barangay' => 'Poblacion',
        'postal_code' => '4103',
        'status' => 'active',
        'setup_completed_at' => now(),
    ]);
}

function svcStockItem(Shop $shop, string $name = 'Detergent', int $quantity = 100, float $cost = 5): Inventory
{
    return Inventory::create([
        'shop_id' => $shop->id,
        'name' => $name,
        'sku' => strtoupper(uniqid('SKU')),
        'unit' => 'sachet',
        'quantity' => $quantity,
        'min_stock' => 1,
        'unit_price' => $cost,
        'selling_price' => $cost * 2,
        'status' => 'active',
    ]);
}

function svcService(Shop $shop, array $supplies, string $model = 'per_kg'): ShopService
{
    $service = app(ShopServiceService::class)->create([
        'shop_id' => $shop->id,
        'service_name' => 'Wash & Fold '.uniqid(),
        'category' => 'full_service',
        'is_active' => true,
        'pricing_model' => $model,
        'price_per_kg' => $model === 'per_kg' ? 60 : null,
        'bundle_price' => $model === 'per_bundle' ? 300 : null,
        'bundle_weight_kg' => $model === 'per_bundle' ? 5 : null,
        'uses_supplies' => true,
        'supplies' => $supplies,
    ]);

    return $service;
}

function svcOrder(Shop $shop, ShopService $service, float $weight = 5, array $overrides = []): ShopOrder
{
    return ShopOrder::create(array_merge([
        'shop_id' => $shop->id,
        'order_source' => 'shop',
        'service_id' => $service->id,
        'order_number' => 'ORD-'.strtoupper(uniqid()),
        'customer_name' => 'Maria S.',
        'pickup_type' => 'walk_in',
        'pricing_model' => $service->pricing_model,
        'price_per_kg' => $service->price_per_kg,
        'estimated_weight_kg' => $weight,
        'total_amount' => 300,
        'payment_status' => 'unpaid',
        'amount_paid' => 0,
        'status' => 'pending',
    ], $overrides))->load('service');
}

it('deducts a per-kg service supplies, logs the movement and records an expense', function () {
    $shop = svcShop();
    $item = svcStockItem($shop, quantity: 100, cost: 5);
    $service = svcService($shop, [['inventory_id' => $item->id, 'quantity' => 0.5, 'unit' => 'sachet']]);
    $order = svcOrder($shop, $service, 5);

    app(ServiceSupplyUsageService::class)->sync($order, null);

    // 0.5 per kg x 5 kg = 2.5, rounded up to whole units.
    expect($item->fresh()->quantity)->toBe(97);

    $movement = InventoryMovement::where('inventory_id', $item->id)->first();
    expect($movement->type)->toBe('usage')
        ->and($movement->quantity)->toBe(-3)
        ->and($movement->reference_number)->toBe($order->order_number);

    $expense = Expense::where('shop_order_id', $order->id)->first();
    expect((float) $expense->amount)->toBe(15.0)
        ->and($expense->category->name)->toBe('Supplies')
        ->and($expense->shop_id)->toBe($shop->id);

    expect($order->serviceSupplies()->count())->toBe(1)
        ->and($order->supplies()->count())->toBe(0)
        ->and((float) $order->fresh()->total_amount)->toBe(300.0);
});

it('scales a bundle service by the number of bundles', function () {
    $shop = svcShop();
    $item = svcStockItem($shop, quantity: 50);
    $service = svcService($shop, [['inventory_id' => $item->id, 'quantity' => 2, 'unit' => 'sachet']], 'per_bundle');
    $order = svcOrder($shop, $service, 10, ['pricing_model' => 'per_bundle', 'bundle_quantity' => 2]);

    app(ServiceSupplyUsageService::class)->sync($order, null);

    expect($item->fresh()->quantity)->toBe(46);
});

it('refuses an order when a supply is out of stock and changes nothing', function () {
    $shop = svcShop();
    $item = svcStockItem($shop, quantity: 1);
    $service = svcService($shop, [['inventory_id' => $item->id, 'quantity' => 1, 'unit' => 'sachet']]);
    $order = svcOrder($shop, $service, 5);

    expect(fn () => app(ServiceSupplyUsageService::class)->sync($order, null))
        ->toThrow(ValidationException::class);

    expect($item->fresh()->quantity)->toBe(1)
        ->and(InventoryMovement::where('inventory_id', $item->id)->count())->toBe(0)
        ->and(Expense::where('shop_order_id', $order->id)->exists())->toBeFalse();
});

it('puts supplies back and removes the expense when the order is cancelled, and takes them again if reopened', function () {
    $shop = svcShop();
    $item = svcStockItem($shop, quantity: 100);
    $service = svcService($shop, [['inventory_id' => $item->id, 'quantity' => 1, 'unit' => 'sachet']]);
    $order = svcOrder($shop, $service, 4);
    $usage = app(ServiceSupplyUsageService::class);

    $usage->sync($order, null);
    expect($item->fresh()->quantity)->toBe(96);

    $order->update(['status' => 'cancelled']);
    $usage->sync($order->fresh()->load('service'), null);

    expect($item->fresh()->quantity)->toBe(100)
        ->and(Expense::where('shop_order_id', $order->id)->exists())->toBeFalse()
        ->and($order->serviceSupplies()->count())->toBe(0)
        ->and(InventoryMovement::where('inventory_id', $item->id)->where('type', 'return')->exists())->toBeTrue();

    $order->update(['status' => 'pending']);
    $usage->sync($order->fresh()->load('service'), null);

    expect($item->fresh()->quantity)->toBe(96);
});

it('adjusts stock and the expense when the weight changes, and does nothing when nothing changed', function () {
    $shop = svcShop();
    $item = svcStockItem($shop, quantity: 100, cost: 2);
    $service = svcService($shop, [['inventory_id' => $item->id, 'quantity' => 1, 'unit' => 'sachet']]);
    $order = svcOrder($shop, $service, 4);
    $usage = app(ServiceSupplyUsageService::class);

    $usage->sync($order, null);
    $usage->sync($order->fresh()->load('service'), null);
    expect(InventoryMovement::where('inventory_id', $item->id)->count())->toBe(1);

    $order->update(['actual_weight_kg' => 6]);
    $usage->sync($order->fresh()->load('service'), null);

    expect($item->fresh()->quantity)->toBe(94)
        ->and((float) Expense::where('shop_order_id', $order->id)->value('amount'))->toBe(12.0)
        ->and(Expense::where('shop_order_id', $order->id)->count())->toBe(1);
});

it('does not let a service use another shops inventory', function () {
    $shop = svcShop();
    $other = svcShop();
    $foreign = svcStockItem($other);

    $request = new App\Http\Requests\Shop\Operations\StoreShopServiceRequest;
    $request->setUserResolver(fn () => $shop->owner);
    $request->merge([
        'service_name' => 'X', 'category' => 'full_service', 'pricing_model' => 'per_kg', 'price_per_kg' => 60,
        'uses_supplies' => true,
        'supplies' => [['inventory_id' => $foreign->id, 'quantity' => 1, 'unit' => 'sachet']],
    ]);

    $validator = validator($request->all(), $request->rules());

    expect($validator->fails())->toBeTrue()
        ->and($validator->errors()->has('supplies.0.inventory_id'))->toBeTrue();
});

it('lets a shop add its own category, only for itself, and protects one that is in use', function () {
    $shop = svcShop();
    $other = svcShop();
    $categories = app(ServiceCategoryService::class);

    $created = $categories->create($shop->id, ['name' => 'Shoe Cleaning']);

    expect($created->key)->toBe('custom_shoe_cleaning')
        ->and($categories->allowedKeys($shop->id))->toContain('custom_shoe_cleaning', 'full_service')
        ->and($categories->allowedKeys($other->id))->not->toContain('custom_shoe_cleaning');

    expect(fn () => $categories->create($shop->id, ['name' => 'shoe cleaning']))->toThrow(ValidationException::class);

    ShopService::create([
        'shop_id' => $shop->id, 'service_name' => 'Sneakers', 'category' => 'custom_shoe_cleaning',
        'pricing_model' => 'per_kg', 'price_per_kg' => 100, 'is_active' => true,
    ]);

    expect(fn () => $categories->delete($shop->id, 'custom_shoe_cleaning'))->toThrow(ValidationException::class)
        ->and(fn () => $categories->delete($other->id, 'custom_shoe_cleaning'))->toThrow(Illuminate\Database\Eloquent\ModelNotFoundException::class);
});

it('shows customers the supplies of a service without costs or stock levels', function () {
    $shop = svcShop();
    $item = svcStockItem($shop, 'Fabric Softener', quantity: 40, cost: 7);
    $service = svcService($shop, [['inventory_id' => $item->id, 'quantity' => 1, 'unit' => 'cap']]);

    $cards = app(CustomerShopCatalogService::class)->serviceCards(
        $shop->id,
        ShopService::where('shop_id', $shop->id)->with('supplies:id,name,unit')->get(),
    );

    expect($cards[0]['supplies'])->toBe([['name' => 'Fabric Softener', 'quantity' => 1.0, 'unit' => 'cap', 'per' => 'kg']])
        ->and(json_encode($cards))->not->toContain('unit_price')->not->toContain('selling_price');
});
