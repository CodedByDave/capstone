<?php

use App\Enums\AccountType;
use App\Http\Middleware\EnforceOwnerPlatformPermissions;
use App\Http\Middleware\EnsureCurrentBusinessAgreementAccepted;
use App\Models\Expense;
use App\Models\Inventory;
use App\Models\Order;
use App\Models\PurchaseOrder;
use App\Models\Shop;
use App\Models\Supplier;
use App\Models\SupplierItem;
use App\Models\User;
use Inertia\Testing\AssertableInertia as Assert;

beforeEach(function () {
    $this->withoutMiddleware([
        EnsureCurrentBusinessAgreementAccepted::class,
        EnforceOwnerPlatformPermissions::class,
    ]);
});

/** A subscribed shop that buys from suppliers, one supplier with a login, and two of its supplies. */
function poShop(string $name = 'PO Laundry'): array
{
    $owner = User::factory()->create(['role' => AccountType::ShopOwner->value]);
    $shop = Shop::create([
        'owner_id' => $owner->id,
        'shop_name' => $name,
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
        'shop_name' => $name,
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
    $login = User::factory()->create(['role' => AccountType::Supplier->value, 'shop_id' => $shop->id]);
    $supplier = Supplier::create([
        'shop_id' => $shop->id,
        'user_id' => $login->id,
        'name' => "{$name} Supplier",
        'email' => $login->email,
        'lead_time_days' => 3,
        'status' => 'active',
    ]);
    $detergent = SupplierItem::create([
        'supplier_id' => $supplier->id, 'name' => 'Surf Liquid Detergent', 'unit' => 'bottle', 'stock_quantity' => 50, 'unit_price' => 160,
    ]);
    $bags = SupplierItem::create([
        'supplier_id' => $supplier->id, 'name' => 'Plastic bags', 'unit' => 'pack', 'stock_quantity' => 100, 'unit_price' => 45,
    ]);
    $stock = Inventory::create([
        'shop_id' => $shop->id,
        'supplier_id' => $supplier->id,
        'name' => 'Surf Liquid Detergent',
        'sku' => 'SKU-'.$shop->id.'-'.uniqid(),
        'unit' => 'ml',
        'package_unit' => 'bottle',
        'package_size' => 3000,
        'quantity' => 1000,
        'min_stock' => 500,
        'unit_price' => 0.0533,
        'status' => 'active',
    ]);

    return compact('owner', 'shop', 'login', 'supplier', 'detergent', 'bags', 'stock');
}

/** The shop owner places a ₱800 + ₱90 order: 5 bottles of detergent into stock, 2 packs of bags. */
function poPlace(array $ctx, bool $send = true): PurchaseOrder
{
    test()->actingAs($ctx['owner'])->post('/shop/purchase-orders', [
        'supplier' => $ctx['supplier']->public_id,
        'notes' => 'Deliver in the morning.',
        'send' => $send,
        'items' => [
            ['supplier_item' => $ctx['detergent']->public_id, 'quantity' => 5, 'inventory_id' => $ctx['stock']->id, 'stock_per_unit' => 3000],
            ['supplier_item' => $ctx['bags']->public_id, 'quantity' => 2],
        ],
    ])->assertSessionHasNoErrors();

    return PurchaseOrder::where('shop_id', $ctx['shop']->id)->latest('id')->firstOrFail();
}

// ─── Placing an order ──────────────────────────────────────────────────────────

test('a shop places an order with its own supplier at the supplier prices', function () {
    $ctx = poShop();

    $order = poPlace($ctx);

    expect($order->status)->toBe('sent')
        ->and((float) $order->total)->toBe(890.0)
        ->and($order->po_number)->toStartWith('PO-')
        ->and($order->items)->toHaveCount(2)
        ->and((float) $order->items->firstWhere('name', 'Surf Liquid Detergent')->line_total)->toBe(800.0);

    // The supplier is told in their portal.
    expect($ctx['login']->notifications()->first()->data['type'])->toBe('purchase_order_sent');
});

test('prices from the browser are ignored', function () {
    $ctx = poShop();

    $this->actingAs($ctx['owner'])->post('/shop/purchase-orders', [
        'supplier' => $ctx['supplier']->public_id,
        'send' => true,
        'items' => [['supplier_item' => $ctx['bags']->public_id, 'quantity' => 1, 'unit_price' => 1]],
    ])->assertSessionHasNoErrors();

    expect((float) PurchaseOrder::sole()->total)->toBe(45.0);
});

test('a draft is not shown to the supplier until it is sent', function () {
    $ctx = poShop();
    $order = poPlace($ctx, send: false);

    expect($order->status)->toBe('draft')
        ->and($ctx['login']->notifications()->count())->toBe(0);

    $this->actingAs($ctx['login'])->get('/supplier-portal')
        ->assertInertia(fn (Assert $page) => $page->where('purchaseOrders.orders', []));

    $this->actingAs($ctx['owner'])->post("/shop/purchase-orders/{$order->public_id}/send")->assertSessionHasNoErrors();

    expect($order->fresh()->status)->toBe('sent');
    $this->actingAs($ctx['login'])->get('/supplier-portal')
        ->assertInertia(fn (Assert $page) => $page
            ->where('purchaseOrders.awaiting', 1)
            ->where('purchaseOrders.orders.0.po_number', $order->po_number));
});

test('a shop cannot order from another shop supplier', function () {
    $mine = poShop('Mine');
    $other = poShop('Other');

    $this->actingAs($mine['owner'])->post('/shop/purchase-orders', [
        'supplier' => $other['supplier']->public_id,
        'send' => true,
        'items' => [['supplier_item' => $other['bags']->public_id, 'quantity' => 1]],
    ])->assertNotFound();

    expect(PurchaseOrder::count())->toBe(0);
});

test('an item the supplier does not sell is refused', function () {
    $mine = poShop('Mine');
    $other = poShop('Other');

    $this->actingAs($mine['owner'])->post('/shop/purchase-orders', [
        'supplier' => $mine['supplier']->public_id,
        'send' => true,
        'items' => [['supplier_item' => $other['bags']->public_id, 'quantity' => 1]],
    ])->assertSessionHasErrors('items.0.supplier_item');

    expect(PurchaseOrder::count())->toBe(0);
});

test('stock can only go into the shop own inventory', function () {
    $mine = poShop('Mine');
    $other = poShop('Other');

    $this->actingAs($mine['owner'])->post('/shop/purchase-orders', [
        'supplier' => $mine['supplier']->public_id,
        'send' => true,
        'items' => [['supplier_item' => $mine['bags']->public_id, 'quantity' => 1, 'inventory_id' => $other['stock']->id]],
    ])->assertSessionHasErrors('items.0.inventory_id');
});

test('an inactive supplier cannot be ordered from', function () {
    $ctx = poShop();
    $ctx['supplier']->update(['status' => 'inactive']);

    $this->actingAs($ctx['owner'])->post('/shop/purchase-orders', [
        'supplier' => $ctx['supplier']->public_id,
        'send' => true,
        'items' => [['supplier_item' => $ctx['bags']->public_id, 'quantity' => 1]],
    ])->assertNotFound();
});

test('the order is checked', function (array $input, string $field) {
    $ctx = poShop();

    $this->actingAs($ctx['owner'])
        ->post('/shop/purchase-orders', array_merge(['supplier' => $ctx['supplier']->public_id, 'send' => true], $input))
        ->assertSessionHasErrors($field);
})->with([
    'no items' => [['items' => []], 'items'],
    'zero quantity' => [['items' => [['supplier_item' => '01JZZZZZZZZZZZZZZZZZZZZZZZ', 'quantity' => 0]]], 'items.0.quantity'],
]);

// ─── Supplier answers ──────────────────────────────────────────────────────────

test('the supplier confirms with a delivery date and the shop is told', function () {
    $ctx = poShop();
    $order = poPlace($ctx);

    $this->actingAs($ctx['login'])
        ->post("/supplier-portal/purchase-orders/{$order->public_id}/answer", [
            'answer' => 'confirm',
            'expected_date' => now()->addDays(2)->toDateString(),
        ])->assertSessionHasNoErrors();

    $order->refresh();
    expect($order->status)->toBe('confirmed')
        ->and($order->expected_date->toDateString())->toBe(now()->addDays(2)->toDateString())
        ->and($ctx['owner']->notifications()->first()->data['type'])->toBe('purchase_order_confirmed');
});

test('the supplier declines with a reason', function () {
    $ctx = poShop();
    $order = poPlace($ctx);

    $this->actingAs($ctx['login'])
        ->post("/supplier-portal/purchase-orders/{$order->public_id}/answer", ['answer' => 'decline', 'note' => 'Out of stock this week.'])
        ->assertSessionHasNoErrors();

    expect($order->fresh()->status)->toBe('declined')
        ->and($order->fresh()->supplier_note)->toBe('Out of stock this week.');
});

test('declining needs a reason and confirming needs a date', function () {
    $ctx = poShop();
    $order = poPlace($ctx);
    $url = "/supplier-portal/purchase-orders/{$order->public_id}/answer";

    $this->actingAs($ctx['login'])->post($url, ['answer' => 'decline'])->assertSessionHasErrors('note');
    $this->actingAs($ctx['login'])->post($url, ['answer' => 'confirm'])->assertSessionHasErrors('expected_date');

    expect($order->fresh()->status)->toBe('sent');
});

test('a supplier cannot answer another supplier order or a draft', function () {
    $mine = poShop('Mine');
    $other = poShop('Other');
    $order = poPlace($mine);
    $draft = poPlace($mine, send: false);

    $this->actingAs($other['login'])
        ->post("/supplier-portal/purchase-orders/{$order->public_id}/answer", ['answer' => 'decline', 'note' => 'x'])
        ->assertNotFound();
    $this->actingAs($mine['login'])
        ->post("/supplier-portal/purchase-orders/{$draft->public_id}/answer", ['answer' => 'decline', 'note' => 'x'])
        ->assertNotFound();

    expect($order->fresh()->status)->toBe('sent');
});

// ─── Receiving ─────────────────────────────────────────────────────────────────

test('receiving adds the stock and records one expense at the supplier prices', function () {
    $ctx = poShop();
    $order = poPlace($ctx);

    $this->actingAs($ctx['owner'])
        ->post("/shop/purchase-orders/{$order->public_id}/receive")
        ->assertSessionHasNoErrors();

    $order->refresh();
    // 5 bottles x 3,000 ml added to the 1,000 ml already there.
    expect($order->status)->toBe('received')
        ->and($ctx['stock']->fresh()->quantity)->toBe(16000)
        ->and($order->received_by)->toBe($ctx['owner']->id);

    $expense = Expense::where('reference_number', $order->po_number)->sole();
    expect((float) $expense->amount)->toBe(890.0);
});

test('a short delivery adds and charges only what came', function () {
    $ctx = poShop();
    $order = poPlace($ctx);
    $detergentLine = $order->items->firstWhere('name', 'Surf Liquid Detergent');
    $bagsLine = $order->items->firstWhere('name', 'Plastic bags');

    $this->actingAs($ctx['owner'])
        ->post("/shop/purchase-orders/{$order->public_id}/receive", [
            'received' => [$detergentLine->id => 3, $bagsLine->id => 0],
        ])->assertSessionHasNoErrors();

    expect($ctx['stock']->fresh()->quantity)->toBe(10000)
        ->and((float) Expense::where('reference_number', $order->po_number)->sole()->amount)->toBe(480.0)
        ->and((float) $detergentLine->fresh()->received_quantity)->toBe(3.0);
});

test('more than was ordered cannot be received', function () {
    $ctx = poShop();
    $order = poPlace($ctx);
    $line = $order->items->first();

    $this->actingAs($ctx['owner'])
        ->post("/shop/purchase-orders/{$order->public_id}/receive", ['received' => [$line->id => 99]])
        ->assertSessionHasErrors("received.{$line->id}");

    expect($order->fresh()->status)->toBe('sent')
        ->and($ctx['stock']->fresh()->quantity)->toBe(1000);
});

test('an order is received only once, and never after it was declined', function () {
    $ctx = poShop();
    $order = poPlace($ctx);
    $declined = poPlace($ctx);
    $declined->update(['status' => 'declined']);

    $this->actingAs($ctx['owner'])->post("/shop/purchase-orders/{$order->public_id}/receive")->assertSessionHasNoErrors();
    $this->actingAs($ctx['owner'])->post("/shop/purchase-orders/{$order->public_id}/receive")->assertSessionHasErrors('status');
    $this->actingAs($ctx['owner'])->post("/shop/purchase-orders/{$declined->public_id}/receive")->assertSessionHasErrors('status');

    expect($ctx['stock']->fresh()->quantity)->toBe(16000);
});

// ─── Cancelling and access ─────────────────────────────────────────────────────

test('cancelling a sent order tells the supplier', function () {
    $ctx = poShop();
    $order = poPlace($ctx);

    $this->actingAs($ctx['owner'])->post("/shop/purchase-orders/{$order->public_id}/cancel")->assertSessionHasNoErrors();

    expect($order->fresh()->status)->toBe('cancelled')
        ->and($ctx['login']->notifications->pluck('data.type')->all())->toContain('purchase_order_cancelled');
});

test('another shop cannot open or change an order', function () {
    $mine = poShop('Mine');
    $other = poShop('Other');
    $order = poPlace($mine);

    $this->actingAs($other['owner'])->get("/shop/purchase-orders/{$order->public_id}")->assertNotFound();
    $this->actingAs($other['owner'])->post("/shop/purchase-orders/{$order->public_id}/receive")->assertNotFound();

    expect($order->fresh()->status)->toBe('sent');
});

test('the list shows only the shop own orders with counts per status', function () {
    $mine = poShop('Mine');
    $other = poShop('Other');
    poPlace($mine);
    poPlace($mine, send: false);
    poPlace($other);

    $this->actingAs($mine['owner'])->get('/shop/purchase-orders')
        ->assertInertia(fn (Assert $page) => $page
            ->component('shop/inventory/purchase-orders/Index')
            ->where('orders.total', 2)
            ->where('counts.sent', 1)
            ->where('counts.draft', 1));
});

test('the new order form offers only the shop own active suppliers', function () {
    $mine = poShop('Mine');
    poShop('Other');
    Supplier::create(['shop_id' => $mine['shop']->id, 'name' => 'Dormant', 'status' => 'inactive']);

    $this->actingAs($mine['owner'])->get('/shop/purchase-orders/create')
        ->assertInertia(fn (Assert $page) => $page
            ->has('suppliers', 1)
            ->where('suppliers.0.name', 'Mine Supplier')
            ->has('suppliers.0.items', 2));
});
