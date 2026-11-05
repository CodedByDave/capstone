<?php

use App\Enums\AccountType;
use App\Http\Middleware\EnforceOwnerPlatformPermissions;
use App\Http\Middleware\EnsureCurrentBusinessAgreementAccepted;
use App\Models\Shop;
use App\Models\ShopOrder;
use App\Models\ShopService;
use App\Models\User;
use App\Services\OrderReceiptService;
use App\Services\ShopOrderService;

beforeEach(function () {
    $this->withoutMiddleware([
        EnsureCurrentBusinessAgreementAccepted::class,
        EnforceOwnerPlatformPermissions::class,
    ]);
});

function receiptShop(): array
{
    $owner = User::factory()->create(['role' => AccountType::ShopOwner->value]);
    $shop = Shop::create([
        'owner_id' => $owner->id,
        'shop_name' => 'Receipt Laundry '.$owner->id,
        'phone' => '09171234567',
        'municipality' => 'Imus',
        'barangay' => 'Poblacion',
        'postal_code' => '4103',
        'status' => 'active',
        'location_mode' => 'single',
        'setup_completed_at' => now(),
    ]);
    $service = ShopService::create([
        'shop_id' => $shop->id,
        'service_name' => 'Wash and Fold',
        'is_active' => true,
        'pricing_model' => 'per_kg',
        'price_per_kg' => 60,
    ]);

    return compact('owner', 'shop', 'service');
}

function receiptOrder(array $ctx, array $extra = []): ShopOrder
{
    return app(ShopOrderService::class)->createOrder(array_merge([
        'shop_id' => $ctx['shop']->id,
        'customer_name' => 'Maria Cruz',
        'service_id' => $ctx['service']->id,
        'pickup_type' => 'walk_in',
        'pricing_model' => 'per_kg',
        'actual_weight_kg' => 5,
        'price_per_kg' => 60,
        'additional_charges' => 0,
        'discount_amount' => 0,
        'total_amount' => 300,
        'customer_agreement_version' => '1.0',
        'customer_agreement_accepted' => true,
    ], $extra), $ctx['owner']);
}

it('builds a paid receipt with the order lines and total', function () {
    $ctx = receiptShop();
    $order = receiptOrder($ctx, ['amount_paid' => 300, 'payment_method' => 'cash']);

    $receipt = app(OrderReceiptService::class)->forOrder($order);

    expect($receipt['title'])->toBe('Payment Successful')
        ->and($receipt['payment_status'])->toBe('paid')
        ->and($receipt['payment_method'])->toBe('Cash')
        ->and($receipt['amount_paid'])->toBe(300.0)
        ->and($receipt['total'])->toBe(300.0)
        ->and($receipt['balance_due'])->toBe(0.0)
        ->and($receipt['invoice_number'])->toBe('INV-'.$order->order_number)
        ->and($receipt['items'][0]['label'])->toBe('Wash and Fold x5 kg')
        ->and($receipt['items'][0]['amount'])->toBe(300.0);
});

it('shows an unpaid order as an invoice with the balance due', function () {
    $ctx = receiptShop();
    $order = receiptOrder($ctx);

    $receipt = app(OrderReceiptService::class)->forOrder($order);

    expect($receipt['title'])->toBe('Order Invoice')
        ->and($receipt['payment_status_label'])->toBe('Unpaid')
        ->and($receipt['balance_due'])->toBe(300.0);
});

it('opens the receipt right after a walk-in order is created', function () {
    $ctx = receiptShop();
    $order = receiptOrder($ctx);

    $this->actingAs($ctx['owner'])
        ->withSession(['show_receipt' => true])
        ->get("/shop/operations/orders/{$order->payment_public_id}")
        ->assertInertia(fn ($page) => $page
            ->component('shop/operations/orders/Show')
            ->where('openReceipt', true)
            ->where('receipt.invoice_number', 'INV-'.$order->order_number));
});

it('downloads the receipt as a PDF for the shop only', function () {
    $ctx = receiptShop();
    $order = receiptOrder($ctx, ['amount_paid' => 300, 'payment_method' => 'cash']);

    $this->actingAs($ctx['owner'])
        ->get("/shop/operations/orders/{$order->payment_public_id}/receipt")
        ->assertOk()
        ->assertHeader('Content-Type', 'application/pdf');

    $other = receiptShop();
    $this->actingAs($other['owner'])
        ->get("/shop/operations/orders/{$order->payment_public_id}/receipt")
        ->assertNotFound();
});

it('works out the change when the customer hands over more cash than the total', function () {
    $ctx = receiptShop();
    $order = receiptOrder($ctx, ['amount_paid' => 300, 'amount_tendered' => 500, 'payment_method' => 'cash']);

    $payment = $order->payments()->first();
    expect((float) $payment->amount)->toBe(300.0)
        ->and((float) $payment->amount_tendered)->toBe(500.0)
        ->and((float) $payment->change_given)->toBe(200.0)
        ->and((float) $order->fresh()->amount_paid)->toBe(300.0)
        ->and($order->fresh()->payment_status)->toBe('paid');

    $receipt = app(OrderReceiptService::class)->forOrder($order->fresh());
    expect($receipt['amount_tendered'])->toBe(500.0)
        ->and($receipt['change_given'])->toBe(200.0)
        ->and($receipt['balance_due'])->toBe(0.0);
});

it('rejects cash that is less than the payment', function () {
    $ctx = receiptShop();

    expect(fn () => receiptOrder($ctx, ['amount_paid' => 300, 'amount_tendered' => 250]))
        ->toThrow(\Illuminate\Validation\ValidationException::class);
});

it('leaves the cash lines off the receipt when no cash amount was entered', function () {
    $ctx = receiptShop();
    $order = receiptOrder($ctx, ['amount_paid' => 300]);

    $receipt = app(OrderReceiptService::class)->forOrder($order);

    expect($receipt['amount_tendered'])->toBeNull()
        ->and($receipt['change_given'])->toBeNull();
});
