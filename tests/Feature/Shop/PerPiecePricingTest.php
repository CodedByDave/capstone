<?php

use App\Enums\AccountType;
use App\Models\Inventory;
use App\Models\Shop;
use App\Models\ShopOrder;
use App\Models\ShopService;
use App\Models\User;
use App\Services\CustomerShopCatalogService;
use App\Services\OrderBillingBreakdownService;
use App\Services\ServiceSupplyUsageService;
use App\Services\ShopServiceCsvService;
use App\Services\ShopServiceService;
use Illuminate\Http\UploadedFile;
use Illuminate\Validation\ValidationException;

function pieceShop(): Shop
{
    $owner = User::factory()->create(['role' => AccountType::ShopOwner->value]);

    return Shop::create([
        'owner_id' => $owner->id,
        'shop_name' => 'Piece Laundry '.$owner->id,
        'phone' => '09171234567',
        'municipality' => 'Imus',
        'barangay' => 'Poblacion',
        'postal_code' => '4103',
        'status' => 'active',
        'setup_completed_at' => now(),
    ]);
}

function pieceService(Shop $shop, array $overrides = []): ShopService
{
    return app(ShopServiceService::class)->create(array_merge([
        'shop_id' => $shop->id,
        'service_name' => 'Comforter '.uniqid(),
        'category' => 'specialty_bulk',
        'is_active' => true,
        'pricing_model' => 'per_piece',
        'price_per_piece' => 250,
    ], $overrides));
}

it('stores a per-piece service and requires its price', function () {
    $shop = pieceShop();

    $service = pieceService($shop);

    expect($service->fresh()->pricing_model)->toBe('per_piece')
        ->and((float) $service->price_per_piece)->toBe(250.0)
        ->and($service->isPerPiece())->toBeTrue();

    expect(fn () => pieceService($shop, ['price_per_piece' => null]))->toThrow(ValidationException::class);
});

it('prices an estimate and an order by item count, and treats it as final', function () {
    $shop = pieceShop();
    $service = pieceService($shop);
    $billing = app(OrderBillingBreakdownService::class);

    $estimate = $billing->estimate($service, 0, 3);

    expect($estimate['total'])->toBe(750.0)
        ->and($estimate['is_estimate'])->toBeFalse()
        ->and($estimate['service']['unit'])->toBe('piece')
        ->and($estimate['service']['quantity'])->toBe(3.0)
        ->and($estimate['service']['rate'])->toBe(250.0);

    $order = ShopOrder::create([
        'shop_id' => $shop->id, 'order_source' => 'shop', 'service_id' => $service->id,
        'order_number' => 'ORD-'.strtoupper(uniqid()), 'customer_name' => 'Maria S.',
        'pickup_type' => 'walk_in', 'pricing_model' => 'per_piece',
        'price_per_piece' => 250, 'piece_count' => 2, 'total_amount' => 500,
        'payment_status' => 'unpaid', 'amount_paid' => 0, 'status' => 'pending',
    ])->load('service');

    $line = $billing->forOrder($order);

    expect($line['is_estimate'])->toBeFalse()
        ->and($line['service']['amount'])->toBe(500.0)
        ->and($line['service']['unit'])->toBe('piece');
});

it('uses the item count for the supplies a per-piece service needs', function () {
    $shop = pieceShop();
    $item = Inventory::create([
        'shop_id' => $shop->id, 'name' => 'Detergent', 'sku' => strtoupper(uniqid('SKU')), 'unit' => 'sachet',
        'quantity' => 50, 'min_stock' => 1, 'unit_price' => 4, 'selling_price' => 8, 'status' => 'active',
    ]);
    $service = pieceService($shop, [
        'uses_supplies' => true,
        'supplies' => [['inventory_id' => $item->id, 'quantity' => 2, 'unit' => 'sachet']],
    ]);
    $order = ShopOrder::create([
        'shop_id' => $shop->id, 'order_source' => 'shop', 'service_id' => $service->id,
        'order_number' => 'ORD-'.strtoupper(uniqid()), 'customer_name' => 'Maria S.',
        'pickup_type' => 'walk_in', 'pricing_model' => 'per_piece',
        'price_per_piece' => 250, 'piece_count' => 3, 'total_amount' => 750,
        'payment_status' => 'unpaid', 'amount_paid' => 0, 'status' => 'pending',
    ])->load('service');

    app(ServiceSupplyUsageService::class)->sync($order, null);

    // 2 sachets per piece x 3 pieces.
    expect($item->fresh()->quantity)->toBe(44);
});

it('imports and exports per-piece services, and still accepts a file without the new column', function () {
    $shop = pieceShop();
    $csv = app(ShopServiceCsvService::class);

    $result = $csv->import($shop->id, UploadedFile::fake()->createWithContent(
        'services.csv',
        "service_name,category,description,pricing_model,price_per_kg,bundle_price,bundle_weight_kg,estimated_hours,status,price_per_piece\n".
        "Comforter,specialty_bulk,,per_piece,,,,48,Active,250\n"
    ));
    expect($result)->toBe(['imported' => 1, 'errors' => []]);

    // An older file has no price_per_piece column at all.
    $older = $csv->import($shop->id, UploadedFile::fake()->createWithContent(
        'old.csv',
        "service_name,category,description,pricing_model,price_per_kg,bundle_price,bundle_weight_kg,estimated_hours,status\n".
        "Wash,full_service,,per_kg,60,,,24,Active\n"
    ));
    expect($older['imported'])->toBe(1);

    $missingPrice = $csv->import($shop->id, UploadedFile::fake()->createWithContent(
        'bad.csv',
        "service_name,category,description,pricing_model,price_per_kg,bundle_price,bundle_weight_kg,estimated_hours,status,price_per_piece\n".
        "Shoes,specialty_bulk,,per_piece,,,,,Active,\n"
    ));
    expect($missingPrice['imported'])->toBe(0)->and($missingPrice['errors'][0])->toContain('Row 2');

    $rows = $csv->exportRows($shop->id);
    $comforter = collect($rows)->firstWhere(0, 'Comforter');
    expect(end($comforter))->toBe('250.00');
});

it('shows a per-piece price as the starting price when a shop has nothing else', function () {
    $shop = pieceShop();
    $services = collect([pieceService($shop, ['price_per_piece' => 180]), pieceService($shop, ['price_per_piece' => 90])]);

    expect(app(CustomerShopCatalogService::class)->startingPrice($services))
        ->toBe(['amount' => 90.0, 'unit' => 'piece']);
});

function pieceOrderPayload(Shop $shop, ShopService $service, array $overrides = []): array
{
    return array_merge([
        'shop_id' => $shop->id,
        'service_id' => $service->id,
        'customer_name' => 'Maria Cruz',
        'customer_phone' => '09181234567',
        'pickup_type' => 'walk_in',
        'payment_method' => 'cash',
        'customer_agreement_version' => app(App\Services\CustomerAgreementService::class)->agreementDataForShopId($shop->id)['version'],
        'customer_agreement_accepted' => true,
    ], $overrides);
}

it('lets a customer order a per-piece service by item count, with the price final and supplies deducted', function () {
    $shop = pieceShop();
    $item = Inventory::create([
        'shop_id' => $shop->id, 'name' => 'Softener', 'sku' => strtoupper(uniqid('SKU')), 'unit' => 'cap',
        'quantity' => 20, 'min_stock' => 1, 'unit_price' => 3, 'selling_price' => 6, 'status' => 'active',
    ]);
    $service = pieceService($shop, [
        'uses_supplies' => true,
        'supplies' => [['inventory_id' => $item->id, 'quantity' => 1, 'unit' => 'cap']],
    ]);
    $customer = User::factory()->create(['role' => AccountType::Customer->value]);

    $this->actingAs($customer)
        ->post('/user/orders', pieceOrderPayload($shop, $service, ['piece_count' => 3]))
        ->assertSessionHasNoErrors();

    $order = ShopOrder::sole();
    expect($order->pricing_model)->toBe('per_piece')
        ->and($order->piece_count)->toBe(3)
        ->and((float) $order->price_per_piece)->toBe(250.0)
        ->and((float) $order->total_amount)->toBe(750.0)
        ->and($order->estimated_weight_kg)->toBeNull()
        ->and($order->priceIsConfirmed())->toBeTrue()
        ->and($item->fresh()->quantity)->toBe(17);
});

it('asks a customer for the piece count of a per-piece service', function () {
    $shop = pieceShop();
    $service = pieceService($shop);
    $customer = User::factory()->create(['role' => AccountType::Customer->value]);

    $this->actingAs($customer)
        ->post('/user/orders', pieceOrderPayload($shop, $service, ['estimated_weight_kg' => 4]))
        ->assertSessionHasErrors('piece_count');

    expect(ShopOrder::count())->toBe(0);
});

it('estimates a per-piece service for the customer from the piece count', function () {
    $shop = pieceShop();
    $service = pieceService($shop);
    $customer = User::factory()->create(['role' => AccountType::Customer->value]);

    $this->actingAs($customer)
        ->getJson('/user/orders/estimate?'.http_build_query([
            'shop_id' => $shop->id, 'service_id' => $service->id, 'piece_count' => 2,
        ]))
        ->assertOk()
        ->assertJsonPath('total', 500)
        ->assertJsonPath('is_estimate', false)
        ->assertJsonPath('service.unit', 'piece');
});
