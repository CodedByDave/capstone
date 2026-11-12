<?php

namespace App\Services;

use App\Models\Inventory;
use App\Models\ShopOrder;
use App\Repositories\InventoryRepository;
use App\Repositories\ShopOrderRepository;
use Illuminate\Validation\ValidationException;

/**
 * The shop items a customer picks when placing an order: add-ons charged on top of a service
 * (e.g. 30 ml extra softener for ₱15) and retail products bought by the pack (e.g. a 1,000 ml
 * bottle for ₱150). Names, amounts and prices always come from the shop's own items, never from
 * what the browser sends.
 */
class CustomerAdditionalChargeService
{
    public const MAX_PER_ITEM = 10;

    /** The most units one order line can store (shop_order_supplies.quantity_used is decimal(8,2)). */
    public const MAX_LINE_QUANTITY = 999999;

    public function __construct(
        private readonly InventoryRepository $inventory,
        private readonly ShopOrderRepository $orders,
        private readonly InventoryService $stock,
    ) {}

    /**
     * @param  list<array{id: int|string, count: int|string}>  $selection  What the customer picked.
     * @param  bool  $lock  Lock the items' rows, for use inside the order's transaction.
     * @return list<array{item: Inventory, kind: string, count: int, name: string, label: string, quantity: float, unit: string, unit_price: float, amount: float}>
     *
     * @throws ValidationException when a pick is not an add-on this shop offers
     */
    public function resolve(int $shopId, array $selection, bool $lock = false): array
    {
        $counts = $this->countsFrom($selection, 'additional_items', 'additional charge');

        if ($counts === []) {
            return [];
        }

        $items = $this->inventory->addonItemsByIds($shopId, array_keys($counts), $lock)->keyBy('id');
        $lines = [];

        foreach ($counts as $id => $count) {
            $item = $items->get($id);

            if ($item === null || (float) $item->addon_price <= 0 || (int) $item->addon_quantity <= 0) {
                throw ValidationException::withMessages([
                    'additional_items' => 'One of the additional charges you picked is no longer offered by the shop.',
                ]);
            }

            $name = $item->addon_name ?: $item->name;

            $lines[] = [
                'item' => $item,
                'kind' => 'addon',
                'count' => $count,
                'name' => $name,
                // The amount (e.g. 60 ml) already shows how many were added, as before retail products.
                'label' => $name,
                'quantity' => (float) ($count * (int) $item->addon_quantity),
                'unit' => (string) $item->unit,
                'unit_price' => round((float) $item->addon_price / (int) $item->addon_quantity, 8),
                'amount' => round($count * (float) $item->addon_price, 2),
            ];
        }

        return $lines;
    }

    /**
     * The retail products the customer picked, priced by the pack.
     *
     * @param  list<array{id: int|string, count: int|string}>  $selection
     * @return list<array{item: Inventory, kind: string, count: int, name: string, label: string, quantity: float, unit: string, unit_price: float, amount: float}>
     *
     * @throws ValidationException when a pick is not a product this shop sells
     */
    public function resolveRetail(int $shopId, array $selection, bool $lock = false): array
    {
        $counts = $this->countsFrom($selection, 'retail_items', 'product');

        if ($counts === []) {
            return [];
        }

        $items = $this->inventory->retailItemsByIds($shopId, array_keys($counts), $lock)->keyBy('id');
        $lines = [];

        foreach ($counts as $id => $count) {
            $item = $items->get($id);

            if ($item === null || (float) $item->retail_price <= 0 || (int) $item->retail_pack_size <= 0) {
                throw ValidationException::withMessages([
                    'retail_items' => 'One of the products you picked is no longer sold by the shop.',
                ]);
            }

            $name = $item->retail_name ?: $item->name;
            $packSize = (int) $item->retail_pack_size;

            // An order line holds at most 999,999.99 units (e.g. ml).
            if ($count * $packSize > self::MAX_LINE_QUANTITY) {
                throw ValidationException::withMessages([
                    'retail_items' => "That many {$name} cannot go on one order. Pick fewer packs.",
                ]);
            }

            $lines[] = [
                'item' => $item,
                'kind' => 'retail',
                'count' => $count,
                'name' => $name,
                'label' => "{$name} × {$count}",
                'quantity' => (float) ($count * $packSize),
                'unit' => (string) $item->unit,
                'unit_price' => round((float) $item->retail_price / $packSize, 8),
                'amount' => round($count * (float) $item->retail_price, 2),
            ];
        }

        return $lines;
    }

    /**
     * The rows saved on the order: one per stock item. An item picked both as an add-on and as a
     * product (extra softener for the wash and a bottle to take home) becomes a single row with
     * the combined amount, so its stock is checked and taken once and the row still adds up.
     *
     * @param  list<array<string, mixed>>  $lines
     * @return list<array{item: Inventory, field: string, label: string, quantity: float, unit: string, unit_price: float, amount: float}>
     */
    public function supplyRows(array $lines): array
    {
        $rows = [];

        foreach ($lines as $line) {
            $id = $line['item']->id;

            if (! isset($rows[$id])) {
                $rows[$id] = [
                    'item' => $line['item'],
                    'field' => $this->fieldFor($line),
                    'label' => $line['label'],
                    'quantity' => $line['quantity'],
                    'unit' => $line['unit'],
                    'amount' => $line['amount'],
                ];

                continue;
            }

            $rows[$id]['field'] = $line['kind'] === 'retail' ? 'retail_items' : $rows[$id]['field'];
            $rows[$id]['label'] .= ' + '.$line['label'];
            $rows[$id]['quantity'] += $line['quantity'];
            $rows[$id]['amount'] = round($rows[$id]['amount'] + $line['amount'], 2);
        }

        return array_values(array_map(fn (array $row) => [
            ...$row,
            'unit_price' => round($row['amount'] / $row['quantity'], 8),
        ], $rows));
    }

    /**
     * Saves the picked items on a just-placed order and puts them aside from stock, as happens when
     * the shop adds supplies to an order. Call inside the order's transaction, with locked items.
     *
     * @param  list<array<string, mixed>>  $lines  From resolve() and resolveRetail().
     *
     * @throws ValidationException when the shop does not have enough of an item
     */
    public function attachToOrder(ShopOrder $order, array $lines): void
    {
        foreach ($this->supplyRows($lines) as $row) {
            if ($row['quantity'] > self::MAX_LINE_QUANTITY) {
                throw ValidationException::withMessages([
                    $row['field'] => 'That many '.$row['label'].' cannot go on one order. Pick fewer.',
                ]);
            }

            if ((float) $row['item']->quantity < $row['quantity']) {
                $name = $row['item']->retail_name ?: ($row['item']->addon_name ?: $row['item']->name);

                throw ValidationException::withMessages([
                    $row['field'] => "Not enough {$name} in stock right now.",
                ]);
            }

            $this->orders->attachSupply($order, $row['item']->id, $row['quantity'], $row['unit'], $row['unit_price'], $row['label']);
            $this->stock->deductStock($row['item'], $row['quantity']);
        }
    }

    /** @param list<array{amount: float}> $lines */
    public function total(array $lines): float
    {
        return round(array_sum(array_column($lines, 'amount')), 2);
    }

    /**
     * The lines as the billing breakdown lists them (the same shape as an order's supplies).
     *
     * @param  list<array<string, mixed>>  $lines
     * @return list<array{name: string, quantity: float, unit: string, unit_price: float, amount: float}>
     */
    public function breakdownLines(array $lines): array
    {
        return array_map(fn (array $line) => [
            'name' => $line['label'],
            'quantity' => $line['quantity'],
            'unit' => $line['unit'],
            'unit_price' => $line['unit_price'],
            'amount' => $line['amount'],
        ], $lines);
    }

    /** @param array<string, mixed> $line */
    private function fieldFor(array $line): string
    {
        return $line['kind'] === 'retail' ? 'retail_items' : 'additional_items';
    }

    /**
     * Adds up repeated picks of the same item and drops zero counts.
     *
     * @param  list<array{id: int|string, count: int|string}>  $selection
     * @return array<int, int> item id => count
     */
    private function countsFrom(array $selection, string $field, string $noun): array
    {
        $counts = [];
        foreach ($selection as $pick) {
            $id = (int) ($pick['id'] ?? 0);
            $counts[$id] = ($counts[$id] ?? 0) + (int) ($pick['count'] ?? 0);
        }
        $counts = array_filter($counts, fn (int $count) => $count > 0);

        foreach ($counts as $count) {
            if ($count > self::MAX_PER_ITEM) {
                throw ValidationException::withMessages([
                    $field => 'You can add at most '.self::MAX_PER_ITEM." of the same {$noun}.",
                ]);
            }
        }

        return $counts;
    }
}
