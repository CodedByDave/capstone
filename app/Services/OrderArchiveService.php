<?php

namespace App\Services;

use App\Models\ShopOrder;
use App\Repositories\ShopOrderRepository;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * Archiving an order puts the stock it took back on the shelf; restoring it takes that stock out
 * again, so inventory always matches the orders that are still active. Every change is logged as
 * an inventory movement. The order keeps its list of hand-added supplies while it is archived.
 */
class OrderArchiveService
{
    public function __construct(
        private readonly ShopOrderRepository $orders,
        private readonly OrderSupplyStockService $supplyStock,
        private readonly ServiceSupplyUsageService $serviceSupplies,
    ) {}

    public function archive(ShopOrder $order, ?int $userId): void
    {
        DB::transaction(function () use ($order, $userId) {
            if ($this->holdsSupplyStock($order)) {
                $this->supplyStock->returnToStock($order, $userId, "Returned from archived order {$order->order_number}");
            }

            $this->serviceSupplies->release($order, $userId);
            $this->orders->delete($order);
        });
    }

    /**
     * @throws ModelNotFoundException for an order that is not archived or belongs to another shop
     * @throws ValidationException when a supply no longer has enough stock; the order stays archived
     */
    public function restore(string $paymentPublicId, int $shopId, ?int $userId): ShopOrder
    {
        $order = $this->orders->findArchivedForShop($paymentPublicId, $shopId);

        DB::transaction(fn () => $this->restoreOrder($order, $userId));

        return $order;
    }

    /**
     * Restores every archived order of the shop, all or none.
     *
     * @return int How many orders were restored.
     *
     * @throws ValidationException when any order's supplies no longer have enough stock
     */
    public function restoreAll(int $shopId, ?int $userId): int
    {
        return DB::transaction(function () use ($shopId, $userId) {
            $archived = $this->orders->archivedForShop($shopId);
            $archived->each(fn (ShopOrder $order) => $this->restoreOrder($order, $userId));

            return $archived->count();
        });
    }

    private function restoreOrder(ShopOrder $order, ?int $userId): void
    {
        $this->orders->restore($order);

        if ($this->holdsSupplyStock($order)) {
            $this->supplyStock->takeFromStock(
                $order,
                $userId,
                "Used again by restored order {$order->order_number}",
                "Order {$order->order_number} cannot be restored: ",
            );
        }

        // Takes the service's supplies again, as for a new order (a cancelled one takes none).
        $this->serviceSupplies->sync($order->load('service'), $userId);
    }

    /** A cancelled order already put its add-ons and products back on the shelf when it was cancelled. */
    private function holdsSupplyStock(ShopOrder $order): bool
    {
        return $order->supplies_returned_at === null;
    }
}
