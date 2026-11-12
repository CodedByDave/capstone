<?php

// Uses the svcShop/svcStockItem/svcService/svcOrder helpers declared in ServiceSuppliesTest.php.

use App\Http\Middleware\EnforceOwnerPlatformPermissions;
use App\Http\Middleware\EnsureCurrentBusinessAgreementAccepted;
use App\Models\InventoryMovement;
use App\Models\ShopOrder;
use App\Services\OrderArchiveService;
use App\Services\ServiceSupplyUsageService;
use Illuminate\Validation\ValidationException;

beforeEach(function () {
    $this->withoutMiddleware([
        EnsureCurrentBusinessAgreementAccepted::class,
        EnforceOwnerPlatformPermissions::class,
    ]);
});

/** An order whose service took 3 detergent and that had 2 fabric conditioner added by hand. */
function archivableOrder(): array
{
    $shop = svcShop();
    $detergent = svcStockItem($shop, 'Detergent', quantity: 100);
    $conditioner = svcStockItem($shop, 'Conditioner', quantity: 10);
    $service = svcService($shop, [['inventory_id' => $detergent->id, 'quantity' => 0.5, 'unit' => 'sachet']]);
    $order = svcOrder($shop, $service, 5);

    app(ServiceSupplyUsageService::class)->sync($order, null);
    $order->supplies()->attach($conditioner->id, ['quantity_used' => 2, 'unit' => 'sachet', 'unit_price' => 10]);
    $conditioner->update(['quantity' => 8]);

    return [$shop, $order, $detergent, $conditioner];
}

test('archiving an order returns its stock but keeps its list of added supplies', function () {
    [$shop, $order, $detergent, $conditioner] = archivableOrder();

    app(OrderArchiveService::class)->archive($order, $shop->owner_id);

    expect(ShopOrder::find($order->id))->toBeNull()
        ->and($detergent->fresh()->quantity)->toBe(100)
        ->and($conditioner->fresh()->quantity)->toBe(10)
        ->and($order->supplies()->count())->toBe(1);
    expect(InventoryMovement::where('inventory_id', $conditioner->id)->where('type', 'return')->value('quantity'))->toBe(2);
});

test('restoring an order takes its supplies out of stock again', function () {
    [$shop, $order, $detergent, $conditioner] = archivableOrder();
    app(OrderArchiveService::class)->archive($order, $shop->owner_id);

    $this->actingAs($shop->owner)
        ->patch("/shop/operations/orders/{$order->payment_public_id}/restore")
        ->assertSessionHasNoErrors();

    expect(ShopOrder::find($order->id))->not->toBeNull()
        ->and($detergent->fresh()->quantity)->toBe(97)
        ->and($conditioner->fresh()->quantity)->toBe(8)
        ->and($order->serviceSupplies()->count())->toBe(1)
        ->and($order->supplies()->count())->toBe(1);
});

test('an order is not restored when its supplies ran out meanwhile', function () {
    [$shop, $order, , $conditioner] = archivableOrder();
    app(OrderArchiveService::class)->archive($order, $shop->owner_id);
    $conditioner->update(['quantity' => 1]);

    expect(fn () => app(OrderArchiveService::class)->restore($order->payment_public_id, $shop->id, $shop->owner_id))
        ->toThrow(ValidationException::class);

    expect(ShopOrder::find($order->id))->toBeNull()
        ->and($conditioner->fresh()->quantity)->toBe(1);
});

test('another shop\'s archived order cannot be restored, one by one or all at once', function () {
    [, $order] = archivableOrder();
    $order->delete();
    $intruder = svcShop();

    $this->actingAs($intruder->owner)
        ->patch("/shop/operations/orders/{$order->payment_public_id}/restore")
        ->assertNotFound();
    $this->actingAs($intruder->owner)->post('/shop/operations/orders/restore-all');

    expect(ShopOrder::find($order->id))->toBeNull();
});

test('restore all brings back every archived order of the shop', function () {
    [$shop, $order] = archivableOrder();
    app(OrderArchiveService::class)->archive($order, $shop->owner_id);

    $this->actingAs($shop->owner)->post('/shop/operations/orders/restore-all')->assertSessionHasNoErrors();

    expect(ShopOrder::find($order->id))->not->toBeNull();
});
