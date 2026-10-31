<?php

use App\Enums\AccountType;
use App\Http\Middleware\EnforceOwnerPlatformPermissions;
use App\Http\Middleware\EnsureCurrentBusinessAgreementAccepted;
use App\Models\Expense;
use App\Models\Inventory;
use App\Models\InventoryMovement;
use App\Models\Order;
use App\Models\Shop;
use App\Models\ShopOrder;
use App\Models\ShopService;
use App\Models\User;
use App\Services\ShopInsightService;
use Inertia\Testing\AssertableInertia as Assert;

beforeEach(function () {
    $this->withoutMiddleware([
        EnsureCurrentBusinessAgreementAccepted::class,
        EnforceOwnerPlatformPermissions::class,
    ]);
});

function dssShop(): Shop
{
    $owner = User::factory()->create(['role' => AccountType::ShopOwner->value]);

    $shop = Shop::create([
        'owner_id' => $owner->id,
        'shop_name' => 'DSS Laundry '.$owner->id,
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
        'plan_name' => 'Premium',
        'billing_months' => 1,
        'is_trial' => false,
        'total_price' => 2239,
        'status' => 'paid',
        'expires_at' => now()->addMonth(),
    ]);

    return $shop;
}

function dssOrder(Shop $shop, array $overrides = []): ShopOrder
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

function dssExpense(Shop $shop, float $amount, ?string $date = null): Expense
{
    return Expense::create([
        'shop_id' => $shop->id,
        'title' => 'Rent',
        'amount' => $amount,
        'expense_date' => $date ?? now()->toDateString(),
    ]);
}

function insightKeys(Shop $shop): array
{
    return array_column(app(ShopInsightService::class)->forShop($shop->id), 'key');
}

it('reports all clear when nothing needs attention', function () {
    $insights = app(ShopInsightService::class)->forShop(dssShop()->id);

    expect($insights)->toHaveCount(1)
        ->and($insights[0]['key'])->toBe('clear')
        ->and($insights[0]['tone'])->toBe('ok');
});

it('ranks urgent insights ahead of lower severity ones', function () {
    $shop = dssShop();
    dssOrder($shop, ['status' => 'in_progress', 'estimated_completion_at' => now()->subHour()]);
    dssOrder($shop);

    $insights = app(ShopInsightService::class)->forShop($shop->id);

    expect($insights[0]['key'])->toBe('overdue')
        ->and($insights[0]['tone'])->toBe('urgent')
        ->and(array_column($insights, 'key'))->toContain('unpaid')
        ->and($insights[0])->toHaveKeys(['module', 'title', 'message', 'rule', 'href', 'action']);
});

it('flags spending above income this month', function () {
    $shop = dssShop();
    dssOrder($shop, ['amount_paid' => 300, 'payment_status' => 'paid', 'paid_at' => now()]);
    dssExpense($shop, 1000);

    expect(insightKeys($shop))->toContain('loss');
});

it('does not flag spending when income covers it', function () {
    $shop = dssShop();
    dssOrder($shop, ['amount_paid' => 300, 'payment_status' => 'paid', 'paid_at' => now()]);
    dssExpense($shop, 100);

    expect(insightKeys($shop))->not->toContain('loss');
});

it('warns when a supply will run out within a week at its recent usage', function () {
    $shop = dssShop();
    $item = Inventory::create([
        'shop_id' => $shop->id,
        'name' => 'Detergent',
        'sku' => 'SKU-'.uniqid(),
        'unit' => 'pcs',
        'quantity' => 20,
        'min_stock' => 1,
        'status' => 'active',
    ]);
    InventoryMovement::create([
        'inventory_id' => $item->id,
        'shop_id' => $shop->id,
        'type' => 'usage',
        'quantity' => -150,
        'quantity_before' => 170,
        'quantity_after' => 20,
    ]);

    $insights = app(ShopInsightService::class)->forShop($shop->id);
    $runway = collect($insights)->firstWhere('key', 'stock-runway');

    expect($runway)->not->toBeNull()
        ->and($runway['title'])->toContain('Detergent');
});

it('keeps insights scoped to the shop', function () {
    $shop = dssShop();
    $other = dssShop();
    dssOrder($other, ['status' => 'in_progress', 'estimated_completion_at' => now()->subHour()]);
    dssExpense($other, 5000);

    expect(insightKeys($shop))->toBe(['clear']);
});

it('serves the insights on the shop dashboard summary', function () {
    $shop = dssShop();
    dssOrder($shop, ['status' => 'in_progress', 'estimated_completion_at' => now()->subHour()]);

    $summary = app(\App\Services\ShopDashboardService::class)->dailySummary($shop->id);

    expect($summary['insights'][0]['key'])->toBe('overdue');
});

it('shows the finance report for the owner shop only', function () {
    $shop = dssShop();
    $other = dssShop();
    dssOrder($shop, ['amount_paid' => 500, 'payment_status' => 'paid', 'paid_at' => now()]);
    dssExpense($shop, 200);
    dssOrder($other, ['amount_paid' => 9000, 'payment_status' => 'paid', 'paid_at' => now()]);
    dssExpense($other, 8000);

    $this->actingAs($shop->owner)
        ->get(route('reports.finance', ['period' => 'year']))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('shop/reports/Finance')
            ->where('stats.revenue', 500)
            ->where('stats.expenses', 200)
            ->where('stats.net', 300)
            ->where('stats.margin', 60)
            ->has('monthlyTrend', 6)
            ->has('expenseCategories', 1));
});

it('shows the operations report for the owner shop only', function () {
    $shop = dssShop();
    $other = dssShop();
    dssOrder($shop, ['status' => 'completed', 'completed_at' => now()->addHours(4)]);
    dssOrder($shop, ['status' => 'cancelled']);
    dssOrder($other);

    $this->actingAs($shop->owner)
        ->get(route('reports.operations', ['period' => 'year']))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('shop/reports/Operations')
            ->where('stats.total', 2)
            ->where('stats.completed', 1)
            ->where('stats.cancelled', 1)
            ->where('stats.completion_rate', 50)
            ->has('weekdayDistribution', 7)
            ->has('services', 1));
});

it('shows the pricing report with margins and suggested prices', function () {
    $shop = dssShop();
    $service = ShopService::create([
        'shop_id' => $shop->id,
        'service_name' => 'Cheap Wash',
        'is_active' => true,
        'pricing_model' => 'per_kg',
        'price_per_kg' => 20,
    ]);
    $soap = Inventory::create([
        'shop_id' => $shop->id,
        'name' => 'Soap',
        'sku' => 'SOAP-1',
        'unit' => 'pcs',
        'quantity' => 100,
        'min_stock' => 5,
        'unit_price' => 30,
        'status' => 'active',
    ]);
    $service->supplies()->attach($soap->id, ['quantity' => 1, 'unit' => 'pcs']);

    $this->actingAs($shop->owner)
        ->get(route('reports.pricing', ['period' => 'year']))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('shop/reports/Pricing')
            ->where('stats.below_cost', 1)
            ->where('services.0.name', 'Cheap Wash')
            ->where('services.0.margin', -10)
            ->where('services.0.suggested_price', 50));
});

it('rejects an unknown report period', function () {
    $shop = dssShop();

    $this->actingAs($shop->owner)
        ->get(route('reports.finance', ['period' => 'decade']))
        ->assertSessionHasErrors('period');
});

it('keeps the new reports away from non-owners', function () {
    $this->get(route('reports.finance'))->assertRedirect();
});
