<?php

namespace App\Services;

use App\Models\Inventory;
use App\Models\ShopOrder;
use App\Repositories\InventoryMovementRepository;
use App\Repositories\ServiceSupplyUsageRepository;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * Takes the supplies a service uses out of stock when an order is placed, logs every change as an
 * inventory movement, and keeps one expense per order in step so Finance sees what the order cost.
 * Stock moves in whole units, so a fractional need is rounded up.
 */
class ServiceSupplyUsageService
{
    public function __construct(
        private readonly ServiceSupplyUsageRepository $repository,
        private readonly InventoryMovementRepository $movements,
        private readonly InventoryService $inventory,
    ) {}

    /**
     * Brings stock, movements and the expense in line with what the order's service needs now.
     * Call inside the transaction that saved the order. Nothing happens when the usage is unchanged.
     *
     * @throws ValidationException When a supply does not have enough stock.
     */
    public function sync(ShopOrder $order, ?int $userId): void
    {
        $service = $order->service;

        // An archived service cannot tell what it used, so what was already taken stays as it is.
        if ($service === null) {
            return;
        }

        $desired = $order->status === 'cancelled' ? [] : $this->desiredLines($order);
        $current = $this->repository->serviceRowsOfOrder($order)
            ->mapWithKeys(fn (Inventory $item) => [$item->id => (int) $item->pivot->quantity_used])
            ->all();

        if ($current === collect($desired)->map(fn ($line) => $line['quantity'])->all()) {
            return;
        }

        DB::transaction(function () use ($order, $desired, $userId) {
            $this->release($order, $userId);

            $total = 0.0;
            $names = [];

            foreach ($desired as $inventoryId => $line) {
                $item = $this->repository->lockInventory($order->shop_id, $inventoryId);

                if ($item === null) {
                    continue;
                }

                if ($item->quantity < $line['quantity']) {
                    throw ValidationException::withMessages([
                        'service_id' => "Not enough stock of {$item->name} for this service. Available: {$item->quantity} {$item->unit}, needed: {$line['quantity']}.",
                    ]);
                }

                $movement = $this->movements->createMovement(
                    inventory: $item,
                    type: 'usage',
                    quantity: -$line['quantity'],
                    userId: $userId,
                    referenceNumber: $order->order_number,
                    notes: "Used by service on order {$order->order_number}",
                );

                $this->repository->changeStock($item, -$line['quantity']);
                $this->repository->attachServiceRow($order, $item, $line['quantity'], $line['unit'], $movement->id);
                $this->inventory->alertIfLowStock($item->refresh());

                $total += $line['quantity'] * (float) $item->unit_price;
                $names[] = "{$item->name} x{$line['quantity']}";
            }

            $this->repository->syncExpense($order, round($total, 2), implode(', ', $names), $userId);
        });
    }

    /** Puts back everything the order took for its service, for a cancelled or deleted order. */
    public function release(ShopOrder $order, ?int $userId): void
    {
        $rows = $this->repository->serviceRowsOfOrder($order);

        foreach ($rows as $row) {
            $item = $this->repository->lockInventory($order->shop_id, $row->id);

            if ($item === null) {
                continue;
            }

            $quantity = (int) $row->pivot->quantity_used;

            $this->movements->createMovement(
                inventory: $item,
                type: 'return',
                quantity: $quantity,
                userId: $userId,
                referenceNumber: $order->order_number,
                notes: "Returned from order {$order->order_number}",
            );

            $this->repository->changeStock($item, $quantity);
        }

        $this->repository->detachServiceRows($order);
        $this->repository->syncExpense($order, 0, '', $userId);
    }

    /**
     * @return array<int, array{quantity: int, unit: string}> Whole units per inventory id.
     */
    private function desiredLines(ShopOrder $order): array
    {
        $service = $order->service;
        $weight = (float) ($order->actual_weight_kg ?: $order->estimated_weight_kg);
        $multiplier = match (true) {
            $service->isPerBundle() => $order->bundle_quantity ?: max(1, (int) ceil($weight / max((float) $service->bundle_weight_kg, 0.01))),
            $service->isPerPiece() => (int) $order->piece_count,
            default => $weight,
        };

        $lines = [];

        foreach ($this->repository->suppliesOfService($service) as $supply) {
            $quantity = (int) ceil(round((float) $supply->pivot->quantity * $multiplier, 4));

            if ($quantity > 0) {
                $lines[$supply->id] = ['quantity' => $quantity, 'unit' => (string) $supply->pivot->unit];
            }
        }

        return $lines;
    }
}
