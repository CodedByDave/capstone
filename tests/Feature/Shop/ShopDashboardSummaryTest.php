<?php

use App\Enums\AccountType;
use App\Models\Shop;
use App\Models\ShopOrder;
use App\Models\ShopService;
use App\Models\User;
use App\Services\ShopDashboardService;

function summaryShop(): Shop
{
    $owner = User::factory()->create(['role' => AccountType::ShopOwner->value]);

    return Shop::create([
        'owner_id' => $owner->id,
        'shop_name' => 'Summary Laundry '.$owner->id,
        'phone' => '09171234567',
        'municipality' => 'Imus',
        'barangay' => 'Poblacion',
        'postal_code' => '4103',
        'status' => 'active',
        'setup_completed_at' => now(),
    ]);
}

function summaryOrder(Shop $shop, array $overrides = []): ShopOrder
{
    $service = ShopService::firstOrCreate(
        ['shop_id' => $shop->id, 'service_name' => 'Wash & Fold'],
        ['is_active' => true, 'pricing_model' => 'per_kg', 'price_per_kg' => 60],
    );

    return ShopOrder::create(array_merge([
        'shop_id' => $shop->id,
        'order_source' => 'shop',
        'service_id' => $service->id,
        'order_number' => 'ORD-'.strtoupper(uniqid()),
        'customer_name' => 'Maria S.',
        'pickup_type' => 'walk_in',
        'pricing_model' => 'per_kg',
        'price_per_kg' => 60,
        'estimated_weight_kg' => 5,
        'total_amount' => 300,
        'payment_status' => 'unpaid',
        'amount_paid' => 0,
        'status' => 'pending',
    ], $overrides));
}

it('summarises today for one shop only', function () {
    $shop = summaryShop();
    $other = summaryShop();

    summaryOrder($shop, [
        'status' => 'in_progress',
        'estimated_completion_at' => now()->subHour(),
    ]);
    summaryOrder($shop, [
        'status' => 'completed',
        'payment_status' => 'partial',
        'amount_paid' => 100,
        'paid_at' => now(),
    ]);
    summaryOrder($other, [
        'status' => 'in_progress',
        'estimated_completion_at' => now()->subHour(),
        'amount_paid' => 300,
        'payment_status' => 'paid',
        'paid_at' => now(),
    ]);

    $summary = app(ShopDashboardService::class)->dailySummary($shop->id);

    expect($summary['orders_today'])->toBe(2)
        ->and($summary['open_orders'])->toBe(1)
        ->and($summary['overdue_orders'])->toBe(1)
        ->and($summary['revenue_today'])->toBe(100.0)
        ->and($summary['unpaid_balance'])->toBe(500.0)
        ->and($summary['unpaid_orders'])->toBe(2)
        ->and($summary['daily_revenue'])->toHaveCount(7)
        ->and(last($summary['daily_revenue']))->toBe(['label' => 'Today', 'revenue' => 100.0])
        ->and($summary['due_today'])->toHaveCount(1)
        ->and($summary['due_today'][0]['overdue'])->toBeTrue()
        ->and($summary['busiest_day'])->toBeNull();
});
