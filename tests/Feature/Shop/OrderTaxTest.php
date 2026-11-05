<?php

use App\Enums\AccountType;
use App\Models\Shop;
use App\Models\ShopService;
use App\Models\User;
use App\Services\CustomerAgreementService;
use App\Services\OrderBillingBreakdownService;
use App\Services\OrderReceiptService;
use App\Services\OrderTaxService;
use App\Services\ShopOrderService;

function taxShop(?string $taxType): Shop
{
    $owner = User::factory()->create(['role' => AccountType::ShopOwner->value]);

    $shop = Shop::create([
        'owner_id' => $owner->id,
        'shop_name' => 'Tax Laundry '.$owner->id,
        'phone' => '09171234567',
        'municipality' => 'Imus',
        'barangay' => 'Poblacion',
        'postal_code' => '4103',
        'status' => 'active',
        'setup_completed_at' => now(),
    ]);

    // Set the way an approved KYC review sets it: straight onto the shop row.
    Shop::whereKey($shop->id)->update(['tax_type' => $taxType]);

    return $shop->refresh();
}

function taxService(Shop $shop): ShopService
{
    return ShopService::create([
        'shop_id' => $shop->id, 'service_name' => 'Wash & Fold', 'is_active' => true,
        'pricing_model' => 'per_kg', 'price_per_kg' => 60,
    ]);
}

/** A walk-in order of 3 kg at ₱60 (₱180 before tax), the way the staff form posts it. */
function taxOrder(Shop $shop, array $overrides = [])
{
    $service = taxService($shop);

    return app(ShopOrderService::class)->createOrder(array_merge([
        'shop_id' => $shop->id,
        'customer_name' => 'Maria S.',
        'service_id' => $service->id,
        'pickup_type' => 'walk_in',
        'pricing_model' => 'per_kg',
        'price_per_kg' => 60,
        'actual_weight_kg' => 3,
        'total_amount' => 180,
        'amount_paid' => 0,
        'customer_agreement_version' => app(CustomerAgreementService::class)->agreementDataForShopId($shop->id)['version'],
        'customer_agreement_accepted' => true,
    ], $overrides), $shop->owner);
}

test('the rate follows the tax type from the BIR certificate', function () {
    $tax = app(OrderTaxService::class);

    expect($tax->rateFor('vat'))->toBe(12.0)
        ->and($tax->rateFor('non_vat'))->toBe(3.0)
        ->and($tax->rateFor(null))->toBe(0.0)
        ->and($tax->labelFor(12.0))->toBe('VAT (12%)')
        ->and($tax->labelFor(3.0))->toBe('Percentage tax (3%)')
        ->and($tax->labelFor(0.0))->toBeNull();
});

test('tax rounds to the centavo, half up', function () {
    $tax = app(OrderTaxService::class);

    expect($tax->amountOn(0.50, 3.0))->toBe(0.02)   // 1.5 centavos
        ->and($tax->amountOn(180, 12.0))->toBe(21.6)
        ->and($tax->amountOn(99.99, 12.0))->toBe(12.0)
        ->and($tax->amountOn(0, 12.0))->toBe(0.0)
        ->and($tax->amountOn(100, 0.0))->toBe(0.0);
});

test('a VAT shop adds 12 percent to the order', function () {
    $order = taxOrder(taxShop('vat'));

    expect((float) $order->tax_rate)->toBe(12.0)
        ->and((float) $order->tax_amount)->toBe(21.6)
        ->and((float) $order->total_amount)->toBe(201.6);
});

test('a non-VAT shop adds 3 percent to the order', function () {
    $order = taxOrder(taxShop('non_vat'));

    expect((float) $order->tax_rate)->toBe(3.0)
        ->and((float) $order->tax_amount)->toBe(5.4)
        ->and((float) $order->total_amount)->toBe(185.4);
});

test('a shop without a verified tax type adds no tax', function () {
    $order = taxOrder(taxShop(null));

    expect((float) $order->tax_amount)->toBe(0.0)
        ->and((float) $order->total_amount)->toBe(180.0);
});

test('a payment in full covers the total with tax', function () {
    $order = taxOrder(taxShop('vat'), ['amount_paid' => 201.6]);

    expect($order->payment_status)->toBe('paid')
        ->and((float) $order->amount_paid)->toBe(201.6);
});

test('the customer estimate includes the shop tax', function () {
    $shop = taxShop('vat');
    $service = taxService($shop);

    $estimate = app(OrderBillingBreakdownService::class)->estimateFor($shop->id, $service->id, 3.0);

    expect($estimate['tax'])->toBe(21.6)
        ->and($estimate['tax_label'])->toBe('VAT (12%)')
        ->and($estimate['total'])->toBe(201.6)
        ->and($estimate['balance_due'])->toBe(201.6);
});

test('the customer estimate is tax free for a shop with no tax type', function () {
    $shop = taxShop(null);
    $service = taxService($shop);

    $estimate = app(OrderBillingBreakdownService::class)->estimateFor($shop->id, $service->id, 3.0);

    expect($estimate['tax'])->toBe(0.0)
        ->and($estimate['tax_label'])->toBeNull()
        ->and($estimate['total'])->toBe(180.0);
});

test('tax is taken off the discount-reduced amount and not charged on the delivery fee', function () {
    $shop = taxShop('vat');
    $order = taxOrder($shop, ['discount_amount' => 30, 'total_amount' => 150]);

    // 150 taxable -> 18.00 tax
    expect((float) $order->tax_amount)->toBe(18.0)
        ->and((float) $order->total_amount)->toBe(168.0);

    $order->update(['delivery_fee' => 50, 'total_amount' => 218]);
    $updated = app(ShopOrderService::class)->updateOrder($order->fresh(), [
        'service_id' => $order->service_id,
        'actual_weight_kg' => 3,
        'discount_amount' => 30,
        'status' => $order->status,
    ]);

    // Delivery fee stays outside the taxable amount.
    expect((float) $updated->tax_amount)->toBe(18.0)
        ->and((float) $updated->total_amount)->toBe(218.0);
});

test('editing an order keeps the rate it was placed with, even if the shop changes tax type', function () {
    $shop = taxShop('non_vat');
    $order = taxOrder($shop);

    Shop::whereKey($shop->id)->update(['tax_type' => 'vat']);

    $updated = app(ShopOrderService::class)->updateOrder($order->fresh(), [
        'service_id' => $order->service_id,
        'actual_weight_kg' => 5,
        'status' => $order->status,
    ]);

    // 5 kg x 60 = 300, 3% = 9.00 (not the new shop's 12%)
    expect((float) $updated->tax_rate)->toBe(3.0)
        ->and((float) $updated->tax_amount)->toBe(9.0)
        ->and((float) $updated->total_amount)->toBe(309.0);
});

test('an order from before tax existed stays untaxed when it is edited', function () {
    $shop = taxShop('vat');
    $order = taxOrder($shop);
    $order->update(['tax_rate' => 0, 'tax_amount' => 0, 'total_amount' => 180]);

    $updated = app(ShopOrderService::class)->updateOrder($order->fresh(), [
        'service_id' => $order->service_id,
        'actual_weight_kg' => 4,
        'status' => $order->status,
    ]);

    expect((float) $updated->tax_amount)->toBe(0.0)
        ->and((float) $updated->total_amount)->toBe(240.0);
});

test('the billing breakdown and the receipt show the tax line', function () {
    $order = taxOrder(taxShop('vat'));

    $breakdown = app(OrderBillingBreakdownService::class)->forOrder($order);
    $receipt = app(OrderReceiptService::class)->forOrder($order);

    expect($breakdown['tax'])->toBe(21.6)
        ->and($breakdown['tax_label'])->toBe('VAT (12%)')
        ->and($breakdown['total'])->toBe(201.6)
        ->and(collect($receipt['items'])->firstWhere('label', 'VAT (12%)')['amount'])->toBe(21.6)
        ->and($receipt['total'])->toBe(201.6);
});
