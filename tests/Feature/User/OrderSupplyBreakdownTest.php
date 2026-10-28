<?php

use App\Enums\AccountType;
use App\Http\Middleware\EnforceOwnerPlatformPermissions;
use App\Http\Middleware\EnsureCurrentBusinessAgreementAccepted;
use App\Models\Inventory;
use App\Models\Order;
use App\Models\Shop;
use App\Models\ShopOrder;
use App\Models\ShopService;
use App\Models\User;
use App\Services\OrderPaymentService;
use App\Services\ShopOrderService;

beforeEach(function () {
    $this->withoutMiddleware([
        EnsureCurrentBusinessAgreementAccepted::class,
        EnforceOwnerPlatformPermissions::class,
    ]);
});

function supplyShop(): Shop
{
    $owner = User::factory()->create(['role' => AccountType::ShopOwner->value]);
    $shop = Shop::create([
        'owner_id' => $owner->id,
        'shop_name' => 'Supply Laundry '.$owner->id,
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
        'plan_name' => 'Basic',
        'billing_months' => 1,
        'is_trial' => false,
        'total_price' => 1119,
        'status' => 'paid',
        'expires_at' => now()->addMonth(),
    ]);

    return $shop;
}

function supplyItem(Shop $shop, string $name, float $price): Inventory
{
    return Inventory::create([
        'shop_id' => $shop->id,
        'name' => $name,
        'sku' => 'SKU-'.strtoupper(uniqid()),
        'unit' => 'pcs',
        'quantity' => 100,
        'unit_price' => $price / 2,
        'selling_price' => $price,
    ]);
}

/** A weighed order with the given supplies edited in through the shop's own edit flow. */
function supplyOrder(Shop $shop, User $customer, array $supplies): ShopOrder
{
    $service = ShopService::create([
        'shop_id' => $shop->id,
        'service_name' => 'Wash and Fold',
        'is_active' => true,
        'pricing_model' => 'per_kg',
        'price_per_kg' => 60,
    ]);

    $order = ShopOrder::create([
        'shop_id' => $shop->id,
        'user_id' => $customer->id,
        'order_source' => 'online',
        'service_id' => $service->id,
        'order_number' => 'ONL-'.strtoupper(uniqid()),
        'customer_name' => $customer->name,
        'customer_phone' => '09181234567',
        'pickup_type' => 'walk_in',
        'pricing_model' => 'per_kg',
        'price_per_kg' => 60,
        'estimated_weight_kg' => 3,
        'actual_weight_kg' => 3,
        'total_amount' => 180,
        'status' => 'in_progress',
    ]);

    return supplyEdit($order, $supplies);
}

/** Saves the order like the edit form: the additional charge is what the supplies add up to. */
function supplyEdit(ShopOrder $order, array $supplies, ?float $additional = null): ShopOrder
{
    $charge = $additional ?? collect($supplies)->sum(
        fn ($supply) => (float) Inventory::find($supply['inventory_id'])->selling_price * $supply['quantity_used'],
    );

    return app(ShopOrderService::class)->updateOrder($order, [
        'service_id' => $order->service_id,
        'estimated_weight_kg' => 3,
        'actual_weight_kg' => 3,
        'additional_charges' => $charge,
        'status' => $order->status,
        'supplies' => $supplies,
    ]);
}

function supplyPay(Shop $shop, ShopOrder $order): void
{
    app(OrderPaymentService::class)->recordShopPayment($shop->owner, $order->refresh(), [
        'amount' => 50,
        'method' => 'cash',
    ]);
}

test('the receipt lists the supplies behind the additional charge with their prices', function () {
    $shop = supplyShop();
    $customer = User::factory()->create(['role' => AccountType::Customer->value]);
    $softener = supplyItem($shop, 'Fabric Softener', 15);
    $bleach = supplyItem($shop, 'Stain Remover', 40);
    $order = supplyOrder($shop, $customer, [
        ['inventory_id' => $softener->id, 'quantity_used' => 2, 'unit' => 'pcs'],
        ['inventory_id' => $bleach->id, 'quantity_used' => 1, 'unit' => 'pcs'],
    ]);
    supplyPay($shop, $order);

    $this->actingAs($customer)
        ->get("/user/orders/{$order->payment_public_id}/payment/receipt")
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->where('receipt.additional_charges', 70)
            ->has('receipt.supplies', 2)
            ->where('receipt.supplies.0.name', 'Fabric Softener')
            ->where('receipt.supplies.0.quantity', 2)
            ->where('receipt.supplies.0.unit_price', 15)
            ->where('receipt.supplies.0.amount', 30)
            ->where('receipt.supplies.1.name', 'Stain Remover')
            ->where('receipt.supplies.1.amount', 40)
            ->where('receipt.other_charges', 0));
});

test('a later price change does not alter what the receipt shows', function () {
    $shop = supplyShop();
    $customer = User::factory()->create(['role' => AccountType::Customer->value]);
    $softener = supplyItem($shop, 'Fabric Softener', 15);
    $order = supplyOrder($shop, $customer, [['inventory_id' => $softener->id, 'quantity_used' => 2, 'unit' => 'pcs']]);

    $softener->update(['selling_price' => 99]);
    // Editing the order again keeps what the supply was first charged at.
    $order = supplyEdit($order->refresh(), [['inventory_id' => $softener->id, 'quantity_used' => 2, 'unit' => 'pcs']], 30);
    supplyPay($shop, $order);

    $this->actingAs($customer)
        ->get("/user/orders/{$order->payment_public_id}/payment/receipt")
        ->assertInertia(fn ($page) => $page
            ->where('receipt.supplies.0.unit_price', 15)
            ->where('receipt.supplies.0.amount', 30)
            ->where('receipt.other_charges', 0));
});

test('a manual charge on top of the supplies shows as other charges', function () {
    $shop = supplyShop();
    $customer = User::factory()->create(['role' => AccountType::Customer->value]);
    $softener = supplyItem($shop, 'Fabric Softener', 15);
    $order = supplyOrder($shop, $customer, [['inventory_id' => $softener->id, 'quantity_used' => 2, 'unit' => 'pcs']]);
    $order = supplyEdit($order->refresh(), [['inventory_id' => $softener->id, 'quantity_used' => 2, 'unit' => 'pcs']], 50);
    supplyPay($shop, $order);

    $this->actingAs($customer)
        ->get("/user/orders/{$order->payment_public_id}/payment/receipt")
        ->assertInertia(fn ($page) => $page->where('receipt.additional_charges', 50)->where('receipt.other_charges', 20));
});

test('a supply added before prices were recorded shows without an amount', function () {
    $shop = supplyShop();
    $customer = User::factory()->create(['role' => AccountType::Customer->value]);
    $softener = supplyItem($shop, 'Fabric Softener', 15);
    $order = supplyOrder($shop, $customer, [['inventory_id' => $softener->id, 'quantity_used' => 2, 'unit' => 'pcs']]);
    DB::table('shop_order_supplies')->where('order_id', $order->id)->update(['unit_price' => null]);
    supplyPay($shop, $order);

    $this->actingAs($customer)
        ->get("/user/orders/{$order->payment_public_id}/payment/receipt")
        ->assertInertia(fn ($page) => $page
            ->where('receipt.supplies.0.name', 'Fabric Softener')
            ->where('receipt.supplies.0.unit_price', null)
            ->where('receipt.supplies.0.amount', null)
            ->where('receipt.other_charges', null));
});

test('an order without supplies has an empty list', function () {
    $shop = supplyShop();
    $customer = User::factory()->create(['role' => AccountType::Customer->value]);
    $order = supplyOrder($shop, $customer, []);
    supplyPay($shop, $order);

    $this->actingAs($customer)
        ->get("/user/orders/{$order->payment_public_id}/payment/receipt")
        ->assertInertia(fn ($page) => $page->has('receipt.supplies', 0)->where('receipt.additional_charges', 0));
});

test('the order page billing breakdown also lists the supplies', function () {
    $shop = supplyShop();
    $customer = User::factory()->create(['role' => AccountType::Customer->value]);
    $softener = supplyItem($shop, 'Fabric Softener', 15);
    $order = supplyOrder($shop, $customer, [['inventory_id' => $softener->id, 'quantity_used' => 3, 'unit' => 'pcs']]);

    $this->actingAs($customer)
        ->get("/user/orders/{$order->payment_public_id}")
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->has('order.billing.supplies', 1)
            ->where('order.billing.supplies.0.amount', 45)
            ->where('order.billing.other_charges', 0));
});

test('another customer cannot see the receipt supplies', function () {
    $shop = supplyShop();
    $customer = User::factory()->create(['role' => AccountType::Customer->value]);
    $softener = supplyItem($shop, 'Fabric Softener', 15);
    $order = supplyOrder($shop, $customer, [['inventory_id' => $softener->id, 'quantity_used' => 1, 'unit' => 'pcs']]);
    supplyPay($shop, $order);

    $this->actingAs(User::factory()->create(['role' => AccountType::Customer->value]))
        ->get("/user/orders/{$order->payment_public_id}/payment/receipt")
        ->assertNotFound();
});
