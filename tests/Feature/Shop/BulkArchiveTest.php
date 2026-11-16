<?php

use App\Enums\AccountType;
use App\Http\Middleware\EnforceOwnerPlatformPermissions;
use App\Http\Middleware\EnsureCurrentBusinessAgreementAccepted;
use App\Models\Inventory;
use App\Models\Order;
use App\Models\Shop;
use App\Models\Supplier;
use App\Models\User;

beforeEach(function () {
    $this->withoutMiddleware([
        EnsureCurrentBusinessAgreementAccepted::class,
        EnforceOwnerPlatformPermissions::class,
    ]);
});

function bulkShop(): array
{
    $owner = User::factory()->create(['role' => AccountType::ShopOwner->value]);
    $shop = Shop::create([
        'owner_id' => $owner->id,
        'shop_name' => 'Bulk Laundry '.$owner->id,
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

    return [$owner->fresh(), $shop];
}

it('archives every selected supplier and restores them again', function () {
    [$owner, $shop] = bulkShop();
    $suppliers = collect(['A', 'B', 'C'])->map(fn ($n) => Supplier::create([
        'shop_id' => $shop->id, 'name' => "Supplier {$n}", 'status' => 'active',
    ]));
    // Supplier rows are selected by their public id, never the sequential id.
    $picked = $suppliers->take(2)->pluck('public_id')->all();

    $this->actingAs($owner)
        ->post('/shop/bulk/supplier/archive', ['ids' => $picked])
        ->assertRedirect();

    expect(Supplier::onlyTrashed()->pluck('public_id')->sort()->values()->all())->toBe(collect($picked)->sort()->values()->all())
        ->and(Supplier::count())->toBe(1);

    $this->actingAs($owner)
        ->post('/shop/bulk/supplier/restore', ['ids' => $picked])
        ->assertRedirect();

    expect(Supplier::onlyTrashed()->count())->toBe(0)->and(Supplier::count())->toBe(3);
});

it('archives selected inventory items', function () {
    [$owner, $shop] = bulkShop();
    $items = collect(['Bleach', 'Downy'])->map(fn ($name) => Inventory::create([
        'shop_id' => $shop->id, 'name' => $name, 'unit' => 'pcs', 'quantity' => 5,
        'min_stock' => 1, 'unit_price' => 5, 'status' => 'active',
    ]));

    $this->actingAs($owner)
        ->post('/shop/bulk/inventory/archive', ['ids' => $items->pluck('id')->all()])
        ->assertRedirect();

    expect(Inventory::count())->toBe(0)->and(Inventory::onlyTrashed()->count())->toBe(2);
});

it('never touches another shop\'s records', function () {
    [$owner] = bulkShop();
    [, $otherShop] = bulkShop();
    $foreign = Supplier::create(['shop_id' => $otherShop->id, 'name' => 'Foreign', 'status' => 'active']);

    $this->actingAs($owner)
        ->post('/shop/bulk/supplier/archive', ['ids' => [$foreign->public_id]])
        ->assertRedirect();

    expect(Supplier::find($foreign->id))->not->toBeNull();
});

it('rejects an empty or malformed selection', function () {
    [$owner] = bulkShop();

    $this->actingAs($owner)->post('/shop/bulk/supplier/archive', ['ids' => []])->assertSessionHasErrors('ids');
    $this->actingAs($owner)->post('/shop/bulk/supplier/archive', ['ids' => ['1; drop']])->assertSessionHasErrors('ids.0');
});

it('archives selected orders by public id and restores them', function () {
    [$owner, $shop] = bulkShop();
    $service = App\Models\ShopService::create([
        'shop_id' => $shop->id, 'service_name' => 'Wash', 'is_active' => true,
        'pricing_model' => 'per_kg', 'price_per_kg' => 60,
    ]);
    $make = fn () => app(App\Services\ShopOrderService::class)->createOrder([
        'shop_id' => $shop->id, 'customer_name' => 'Henry', 'customer_phone' => '0917 555 1234',
        'service_id' => $service->id, 'pickup_type' => 'walk_in', 'pricing_model' => 'per_kg',
        'actual_weight_kg' => 5, 'price_per_kg' => 60, 'additional_charges' => 0,
        'discount_amount' => 0, 'total_amount' => 300,
        'customer_agreement_version' => '1.0', 'customer_agreement_accepted' => true,
    ], $owner);
    $a = $make();
    $b = $make();

    $this->actingAs($owner)
        ->post('/shop/bulk/order/archive', ['ids' => [$a->payment_public_id]])
        ->assertRedirect();

    expect(App\Models\ShopOrder::onlyTrashed()->pluck('id')->all())->toBe([$a->id])
        ->and(App\Models\ShopOrder::whereKey($b->id)->exists())->toBeTrue();

    $this->actingAs($owner)
        ->post('/shop/bulk/order/restore', ['ids' => [$a->payment_public_id]])
        ->assertRedirect();

    expect(App\Models\ShopOrder::onlyTrashed()->count())->toBe(0);
});

it('does not archive orders of another shop', function () {
    [$owner] = bulkShop();
    [, $otherShop] = bulkShop();
    $service = App\Models\ShopService::create([
        'shop_id' => $otherShop->id, 'service_name' => 'Wash', 'is_active' => true,
        'pricing_model' => 'per_kg', 'price_per_kg' => 60,
    ]);
    $order = app(App\Services\ShopOrderService::class)->createOrder([
        'shop_id' => $otherShop->id, 'customer_name' => 'Henry', 'customer_phone' => '0917 555 1234',
        'service_id' => $service->id, 'pickup_type' => 'walk_in', 'pricing_model' => 'per_kg',
        'actual_weight_kg' => 5, 'price_per_kg' => 60, 'additional_charges' => 0,
        'discount_amount' => 0, 'total_amount' => 300,
        'customer_agreement_version' => '1.0', 'customer_agreement_accepted' => true,
    ], User::find($otherShop->owner_id));

    $this->actingAs($owner)
        ->post('/shop/bulk/order/archive', ['ids' => [$order->payment_public_id]])
        ->assertRedirect();

    expect(App\Models\ShopOrder::onlyTrashed()->count())->toBe(0);
});

it('bulk archives draft payrolls and leaves finalized ones alone', function () {
    [$owner, $shop] = bulkShop();
    $draft = App\Models\Payroll::create([
        'shop_id' => $shop->id, 'period_label' => 'Oct 1-15', 'period_start' => '2026-10-01',
        'period_end' => '2026-10-15', 'status' => 'draft', 'created_by' => $owner->id,
    ]);
    $final = App\Models\Payroll::create([
        'shop_id' => $shop->id, 'period_label' => 'Oct 16-31', 'period_start' => '2026-10-16',
        'period_end' => '2026-10-31', 'status' => 'finalized', 'created_by' => $owner->id,
    ]);

    $this->actingAs($owner)
        ->post('/shop/bulk/payroll/archive', ['ids' => [$draft->public_id, $final->public_id]])
        ->assertRedirect();

    expect(App\Models\Payroll::onlyTrashed()->pluck('id')->all())->toBe([$draft->id]);
});
