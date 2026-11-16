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
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
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

    $this->actingAs($ctx['login'])->get('/supplier-portal/purchase-orders')
        ->assertInertia(fn (Assert $page) => $page->where('purchaseOrders.orders', []));

    $this->actingAs($ctx['owner'])->post("/shop/purchase-orders/{$order->public_id}/send")->assertSessionHasNoErrors();

    expect($order->fresh()->status)->toBe('sent');
    $this->actingAs($ctx['login'])->get('/supplier-portal/purchase-orders')
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

// ─── Delivery proof and receiving ──────────────────────────────────────────────

function poPhoto(string $name = 'delivery.jpg'): UploadedFile
{
    return UploadedFile::fake()->image($name, 400, 300);
}

/** The supplier confirms the order and hands it over with a photo, so the shop can receive it. */
function poDeliver(array $ctx, PurchaseOrder $order): PurchaseOrder
{
    test()->actingAs($ctx['login'])->post("/supplier-portal/purchase-orders/{$order->public_id}/answer", [
        'answer' => 'confirm',
        'expected_date' => now()->addDay()->toDateString(),
    ])->assertSessionHasNoErrors();

    test()->actingAs($ctx['login'])->post("/supplier-portal/purchase-orders/{$order->public_id}/deliver", [
        'photo' => poPhoto('supplier.jpg'),
    ])->assertSessionHasNoErrors();

    return $order->fresh();
}

/** The shop receives with its own photo, plus any received quantities. */
function poReceive(array $ctx, PurchaseOrder $order, array $extra = [])
{
    return test()->actingAs($ctx['owner'])
        ->post("/shop/purchase-orders/{$order->public_id}/receive", $extra + ['proof' => poPhoto('shop.jpg')]);
}

test('the supplier marks a confirmed order delivered with a photo and the shop is told', function () {
    Storage::fake('local');
    $ctx = poShop();
    $order = poDeliver($ctx, poPlace($ctx));

    expect($order->supplier_delivered_at)->not->toBeNull()
        ->and($order->supplier_proof_path)->not->toBeNull()
        ->and($order->status)->toBe('confirmed')
        ->and($ctx['owner']->notifications->pluck('data.type')->all())->toContain('purchase_order_delivered');
    Storage::disk('local')->assertExists($order->supplier_proof_path);
});

test('delivering needs a photo of the right kind, on a confirmed order, once', function () {
    Storage::fake('local');
    $ctx = poShop();
    $order = poPlace($ctx);
    $url = "/supplier-portal/purchase-orders/{$order->public_id}/deliver";

    // Not confirmed yet.
    $this->actingAs($ctx['login'])->post($url, ['photo' => poPhoto()])->assertSessionHasErrors('status');

    $this->actingAs($ctx['login'])->post("/supplier-portal/purchase-orders/{$order->public_id}/answer", [
        'answer' => 'confirm', 'expected_date' => now()->addDay()->toDateString(),
    ]);

    $this->actingAs($ctx['login'])->post($url, [])->assertSessionHasErrors('photo');
    $this->actingAs($ctx['login'])->post($url, ['photo' => UploadedFile::fake()->create('proof.pdf', 100, 'application/pdf')])->assertSessionHasErrors('photo');
    $this->actingAs($ctx['login'])->post($url, ['photo' => poPhoto()])->assertSessionHasNoErrors();
    $this->actingAs($ctx['login'])->post($url, ['photo' => poPhoto()])->assertSessionHasErrors('status');
});

test('another supplier cannot deliver or view an order that is not theirs', function () {
    Storage::fake('local');
    $mine = poShop('Mine');
    $other = poShop('Other');
    $order = poDeliver($mine, poPlace($mine));

    $this->actingAs($other['login'])->post("/supplier-portal/purchase-orders/{$order->public_id}/deliver", ['photo' => poPhoto()])->assertNotFound();
    $this->actingAs($other['login'])->get("/supplier-portal/purchase-orders/{$order->public_id}/proof/supplier")->assertNotFound();
    $this->actingAs($mine['login'])->get("/supplier-portal/purchase-orders/{$order->public_id}/proof/supplier")->assertOk();
});

test('the shop cannot receive before the supplier has delivered with a photo', function () {
    Storage::fake('local');
    $ctx = poShop();
    $order = poPlace($ctx);

    // Sent but not confirmed.
    poReceive($ctx, $order)->assertSessionHasErrors('status');

    // Confirmed but not yet delivered.
    $this->actingAs($ctx['login'])->post("/supplier-portal/purchase-orders/{$order->public_id}/answer", [
        'answer' => 'confirm', 'expected_date' => now()->addDay()->toDateString(),
    ]);
    poReceive($ctx, $order)->assertSessionHasErrors('status');

    expect($order->fresh()->status)->toBe('confirmed')
        ->and($ctx['stock']->fresh()->quantity)->toBe(1000);
});

test('receiving needs the shop photo', function () {
    Storage::fake('local');
    $ctx = poShop();
    $order = poDeliver($ctx, poPlace($ctx));

    $this->actingAs($ctx['owner'])->post("/shop/purchase-orders/{$order->public_id}/receive")->assertSessionHasErrors('proof');
    $this->actingAs($ctx['owner'])
        ->post("/shop/purchase-orders/{$order->public_id}/receive", ['proof' => UploadedFile::fake()->create('x.pdf', 10, 'application/pdf')])
        ->assertSessionHasErrors('proof');

    expect($order->fresh()->status)->toBe('confirmed');
});

test('receiving adds the stock and records one expense at the supplier prices', function () {
    Storage::fake('local');
    $ctx = poShop();
    $order = poDeliver($ctx, poPlace($ctx));

    poReceive($ctx, $order)->assertSessionHasNoErrors();

    $order->refresh();
    // 5 bottles x 3,000 ml added to the 1,000 ml already there.
    expect($order->status)->toBe('received')
        ->and($ctx['stock']->fresh()->quantity)->toBe(16000)
        ->and($order->received_by)->toBe($ctx['owner']->id)
        ->and($order->received_proof_path)->not->toBeNull();
    Storage::disk('local')->assertExists($order->received_proof_path);

    $expense = Expense::where('reference_number', $order->po_number)->sole();
    expect((float) $expense->amount)->toBe(890.0);
});

test('a short delivery adds and charges only what came', function () {
    Storage::fake('local');
    $ctx = poShop();
    $order = poDeliver($ctx, poPlace($ctx));
    $detergentLine = $order->items->firstWhere('name', 'Surf Liquid Detergent');
    $bagsLine = $order->items->firstWhere('name', 'Plastic bags');

    poReceive($ctx, $order, ['received' => [$detergentLine->id => 3, $bagsLine->id => 0]])->assertSessionHasNoErrors();

    expect($ctx['stock']->fresh()->quantity)->toBe(10000)
        ->and((float) Expense::where('reference_number', $order->po_number)->sole()->amount)->toBe(480.0)
        ->and((float) $detergentLine->fresh()->received_quantity)->toBe(3.0);
});

test('more than was ordered cannot be received and no photo is kept', function () {
    Storage::fake('local');
    $ctx = poShop();
    $order = poDeliver($ctx, poPlace($ctx));
    $line = $order->items->first();

    poReceive($ctx, $order, ['received' => [$line->id => 99]])->assertSessionHasErrors("received.{$line->id}");

    expect($order->fresh()->status)->toBe('confirmed')
        ->and($order->fresh()->received_proof_path)->toBeNull()
        ->and($ctx['stock']->fresh()->quantity)->toBe(1000)
        ->and(Storage::disk('local')->allFiles('purchase-order-proofs/'.$ctx['shop']->id))->toHaveCount(1);
});

test('an order is received only once, and never after it was declined', function () {
    Storage::fake('local');
    $ctx = poShop();
    $order = poDeliver($ctx, poPlace($ctx));
    $declined = poPlace($ctx);
    $declined->update(['status' => 'declined']);

    poReceive($ctx, $order)->assertSessionHasNoErrors();
    poReceive($ctx, $order)->assertSessionHasErrors('status');
    poReceive($ctx, $declined)->assertSessionHasErrors('status');

    expect($ctx['stock']->fresh()->quantity)->toBe(16000);
});

test('the shop opens both photos, and another shop gets a 404', function () {
    Storage::fake('local');
    $mine = poShop('Mine');
    $other = poShop('Other');
    $order = poDeliver($mine, poPlace($mine));
    poReceive($mine, $order)->assertSessionHasNoErrors();

    $this->actingAs($mine['owner'])->get("/shop/purchase-orders/{$order->public_id}/proof/supplier")->assertOk();
    $this->actingAs($mine['owner'])->get("/shop/purchase-orders/{$order->public_id}/proof/received")->assertOk();
    $this->actingAs($mine['owner'])->get("/shop/purchase-orders/{$order->public_id}/proof/other")->assertNotFound();
    $this->actingAs($other['owner'])->get("/shop/purchase-orders/{$order->public_id}/proof/supplier")->assertNotFound();
    $this->actingAs($other['owner'])->get("/shop/purchase-orders/{$order->public_id}/proof/received")->assertNotFound();
});

test('staff need the inventory update permission to receive', function () {
    Storage::fake('local');
    $ctx = poShop();
    $order = poDeliver($ctx, poPlace($ctx));
    $staff = User::factory()->create(['role' => AccountType::Staff->value, 'shop_id' => $ctx['shop']->id]);

    $this->actingAs($staff)
        ->post("/staff/purchase-orders/{$order->public_id}/receive", ['proof' => poPhoto()])
        ->assertForbidden();

    expect($order->fresh()->status)->toBe('confirmed');
});

// ─── Restocking by name, and supplies not in the product list ──────────────────

test('a line with no link is matched to the stock item of the same name and restocked', function () {
    Storage::fake('local');
    $ctx = poShop();
    $this->actingAs($ctx['owner'])->post('/shop/purchase-orders', [
        'supplier' => $ctx['supplier']->public_id,
        'send' => true,
        // No inventory item chosen; the name is "Surf Liquid Detergent", which is in the product list.
        'items' => [['supplier_item' => $ctx['detergent']->public_id, 'quantity' => 2]],
    ])->assertSessionHasNoErrors();
    $order = poDeliver($ctx, PurchaseOrder::latest('id')->firstOrFail());

    poReceive($ctx, $order)->assertSessionHasNoErrors();

    $line = $order->items->first()->fresh();
    // 2 bottles x the item's 3,000 ml package size.
    expect($ctx['stock']->fresh()->quantity)->toBe(7000)
        ->and($line->inventory_id)->toBe($ctx['stock']->id)
        ->and($line->stock_per_unit)->toBe(3000);
});

test('a supply that is not in the product list is flagged, and adding it links it without doubling stock', function () {
    Storage::fake('local');
    $ctx = poShop();
    $order = poDeliver($ctx, poPlace($ctx));
    poReceive($ctx, $order)->assertSessionHasNoErrors();

    $bags = $order->items->firstWhere('name', 'Plastic bags');
    $this->actingAs($ctx['owner'])->get("/shop/purchase-orders/{$order->public_id}")
        ->assertInertia(fn (Assert $page) => $page
            ->where('purchaseOrder.items', fn ($items) => collect($items)->firstWhere('name', 'Plastic bags')['can_list'] === true
                && collect($items)->firstWhere('name', 'Surf Liquid Detergent')['can_list'] === false));

    // The form opens with the received details.
    $line = $order->items->values()->search(fn ($i) => $i->id === $bags->id) + 1;
    $this->actingAs($ctx['owner'])->get("/shop/inventory/create?purchase_order={$order->public_id}&line={$line}")
        ->assertInertia(fn (Assert $page) => $page
            ->where('fromPurchaseOrder.prefill.name', 'Plastic bags')
            ->where('fromPurchaseOrder.prefill.quantity', 2)
            ->where('fromPurchaseOrder.prefill.package_unit', 'pack'));

    $this->actingAs($ctx['owner'])->post('/shop/inventory', [
        'name' => 'Plastic bags', 'unit' => 'pcs', 'package_unit' => 'pack', 'package_size' => 1,
        'quantity' => 2, 'min_stock' => 1, 'unit_price' => 45,
        'purchase_order' => $order->public_id, 'line' => $line,
    ])->assertSessionHasNoErrors()->assertRedirect("/shop/purchase-orders/{$order->public_id}");

    $item = Inventory::where('shop_id', $ctx['shop']->id)->where('name', 'Plastic bags')->sole();
    expect($item->quantity)->toBe(2)
        ->and($bags->fresh()->inventory_id)->toBe($item->id);

    // It cannot be added a second time.
    $this->actingAs($ctx['owner'])->post('/shop/inventory', [
        'name' => 'Plastic bags 2', 'unit' => 'pcs', 'package_unit' => 'pack', 'package_size' => 1,
        'quantity' => 2, 'min_stock' => 1,
        'purchase_order' => $order->public_id, 'line' => $line,
    ])->assertSessionHasErrors('status');
    expect(Inventory::where('shop_id', $ctx['shop']->id)->where('name', 'like', 'Plastic bags%')->count())->toBe(1);
});

test('another shop cannot add a line of this order to its product list', function () {
    Storage::fake('local');
    $mine = poShop('Mine');
    $other = poShop('Other');
    $order = poDeliver($mine, poPlace($mine));
    poReceive($mine, $order)->assertSessionHasNoErrors();

    $this->actingAs($other['owner'])->get("/shop/inventory/create?purchase_order={$order->public_id}&line=2")->assertNotFound();
    $this->actingAs($other['owner'])->post('/shop/inventory', [
        'name' => 'Plastic bags', 'unit' => 'pcs', 'package_unit' => 'pack', 'package_size' => 1,
        'quantity' => 2, 'min_stock' => 1,
        'purchase_order' => $order->public_id, 'line' => 2,
    ])->assertNotFound();
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
    $this->actingAs($other['owner'])
        ->post("/shop/purchase-orders/{$order->public_id}/receive", ['proof' => UploadedFile::fake()->image('p.jpg')])
        ->assertNotFound();

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

test('the order page keeps the shop subscription prop the sidebar reads', function () {
    $ctx = poShop();
    $order = poPlace($ctx);

    // The purchase order must not replace the shared `order` prop, or the sidebar loses its modules.
    $this->actingAs($ctx['owner'])->get("/shop/purchase-orders/{$order->public_id}")
        ->assertInertia(fn (Assert $page) => $page
            ->where('purchaseOrder.public_id', $order->public_id)
            ->where('order.status', 'paid'));
});
