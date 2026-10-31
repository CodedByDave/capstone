<?php

use App\Models\Inventory;
use App\Models\InventoryMovement;
use App\Models\Shop;
use App\Models\Supplier;
use App\Models\User;
use App\Services\ServicePricingService;
use App\Services\ShopInsightService;
use App\Services\StaffingForecastService;
use App\Services\StockReorderService;
use Database\Seeders\DssDemoSeeder;
use Illuminate\Support\Carbon;

function demoShop(): Shop
{
    (new DssDemoSeeder)->run();

    return Shop::where('owner_id', User::where('email', DssDemoSeeder::OWNER_EMAIL)->value('id'))->firstOrFail();
}

it('suggests a reorder quantity from daily use, lead time and cover days', function () {
    $shop = demoShop();
    $detergent = Inventory::where('shop_id', $shop->id)->where('sku', 'DSS-DETERGENT')->firstOrFail();

    $row = app(StockReorderService::class)->forShop($shop->id)[$detergent->id];

    // 540 sachets over 30 days = 18/day, supplier lead time 2 days, 20 in stock:
    // 18 × (2 + 7) − 20 = 142.
    expect($row['daily_use'])->toBe(18.0)
        ->and($row['lead_time_days'])->toBe(2)
        ->and($row['needs_reorder'])->toBeTrue()
        ->and($row['suggested_quantity'])->toBe(142);
});

it('does not flag items whose stock outlasts the supplier lead time', function () {
    $shop = demoShop();
    $bags = Inventory::where('shop_id', $shop->id)->where('sku', 'DSS-BAGS')->firstOrFail();

    $row = app(StockReorderService::class)->forShop($shop->id)[$bags->id];

    expect($row['needs_reorder'])->toBeFalse();
});

it('uses the default lead time for items without a supplier', function () {
    $shop = demoShop();
    $item = Inventory::create([
        'shop_id' => $shop->id,
        'name' => 'Hangers',
        'sku' => 'NO-SUPPLIER',
        'unit' => 'pcs',
        'quantity' => 10,
        'min_stock' => 1,
        'status' => 'active',
    ]);
    InventoryMovement::create([
        'inventory_id' => $item->id,
        'shop_id' => $shop->id,
        'type' => 'usage',
        'quantity' => -30,
        'quantity_before' => 40,
        'quantity_after' => 10,
    ]);

    $row = app(StockReorderService::class)->forShop($shop->id)[$item->id];

    expect($row['lead_time_days'])->toBe(StockReorderService::DEFAULT_LEAD_TIME_DAYS);
});

it('stores a supplier lead time', function () {
    $shop = demoShop();

    expect(Supplier::where('shop_id', $shop->id)->orderBy('id')->pluck('lead_time_days')->all())->toBe([2, 5, 7]);
});

it('forecasts the coming week and flags understaffed and overstaffed days', function () {
    $shop = demoShop();

    $forecast = collect(app(StaffingForecastService::class)->forShop($shop->id))->keyBy('day');

    expect($forecast)->toHaveCount(7)
        ->and($forecast['Sat']['status'])->toBe(StaffingForecastService::UNDERSTAFFED)
        ->and($forecast['Sat']['scheduled_staff'])->toBe(1)
        ->and($forecast['Sat']['required_staff'])->toBeGreaterThan(1)
        ->and($forecast['Sun']['status'])->toBe(StaffingForecastService::UNDERSTAFFED)
        ->and($forecast['Sun']['scheduled_staff'])->toBe(0)
        ->and($forecast['Wed']['status'])->toBe(StaffingForecastService::OVERSTAFFED);
});

it('skips the forecast when there is too little order history', function () {
    $shop = demoShop();
    $empty = Shop::create([
        'owner_id' => User::factory()->create(['role' => 'owner'])->id,
        'shop_name' => 'Empty Laundry',
        'phone' => '09171234567',
        'municipality' => 'Imus',
        'barangay' => 'Poblacion',
        'postal_code' => '4103',
        'status' => 'active',
    ]);

    expect(app(StaffingForecastService::class)->forShop($empty->id, Carbon::now()))->toBe([])
        ->and($shop->id)->not->toBe($empty->id);
});

it('analyzes service pricing against supply cost', function () {
    $shop = demoShop();

    $rows = collect(app(ServicePricingService::class)->analyze($shop->id, now()->subYear(), now()))->keyBy('name');

    expect($rows['Premium Whitening']['status'])->toBe(ServicePricingService::BELOW_COST)
        ->and($rows['Premium Whitening']['supply_cost'])->toBe(44.5)
        ->and($rows['Premium Whitening']['suggested_price'])->toBe(75.0)
        ->and($rows['Comforter Wash']['status'])->toBe(ServicePricingService::LOW_MARGIN)
        ->and($rows['Comforter Wash']['suggested_price'])->toBe(252.0)
        ->and($rows['Wash & Fold']['status'])->toBe(ServicePricingService::HEALTHY)
        ->and($rows['Wash & Fold']['supply_cost'])->toBe(14.5)
        ->and($rows['Wash & Fold']['orders'])->toBeGreaterThan(0);
});

it('marks a service without a supply list instead of calling it free', function () {
    $shop = demoShop();
    App\Models\ShopService::create([
        'shop_id' => $shop->id,
        'service_name' => 'Ironing',
        'is_active' => true,
        'pricing_model' => 'per_kg',
        'price_per_kg' => 30,
    ]);

    $rows = collect(app(ServicePricingService::class)->analyze($shop->id, now()->subYear(), now()))->keyBy('name');

    expect($rows['Ironing']['status'])->toBe(ServicePricingService::NO_SUPPLIES)
        ->and($rows['Ironing']['suggested_price'])->toBeNull();
});

it('makes the demo shop trip every decision support rule', function () {
    $shop = demoShop();

    $keys = array_column(app(ShopInsightService::class)->forShop($shop->id), 'key');

    expect($keys)->toContain(
        'overdue', 'unpaid', 'cancellations', 'service-concentration', 'busiest',
        'stock', 'stock-runway',
        'loss', 'priced-below-cost',
        'attendance', 'payroll-share', 'understaffed', 'overstaffed',
    );
});
