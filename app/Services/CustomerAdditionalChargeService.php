<?php

namespace App\Services;

use App\Models\Inventory;
use App\Repositories\InventoryRepository;
use Illuminate\Validation\ValidationException;

/**
 * The additional charges a customer picks when placing an order: shop items sold as add-ons
 * (e.g. 30 ml extra softener for ₱15). Names, amounts and prices always come from the shop's own
 * items, never from what the browser sends.
 */
class CustomerAdditionalChargeService
{
    public const MAX_PER_ITEM = 10;

    public function __construct(private readonly InventoryRepository $inventory) {}

    /**
     * @param  list<array{id: int|string, count: int|string}>  $selection  What the customer picked.
     * @param  bool  $lock  Lock the items' rows, for use inside the order's transaction.
     * @return list<array{item: Inventory, count: int, name: string, quantity: float, unit: string, unit_price: float, amount: float}>
     *
     * @throws ValidationException when a pick is not an add-on this shop offers
     */
    public function resolve(int $shopId, array $selection, bool $lock = false): array
    {
        $counts = [];
        foreach ($selection as $pick) {
            $id = (int) ($pick['id'] ?? 0);
            $counts[$id] = ($counts[$id] ?? 0) + (int) ($pick['count'] ?? 0);
        }
        $counts = array_filter($counts, fn (int $count) => $count > 0);

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

            if ($count > self::MAX_PER_ITEM) {
                throw ValidationException::withMessages([
                    'additional_items' => 'You can add at most '.self::MAX_PER_ITEM.' of the same additional charge.',
                ]);
            }

            $lines[] = [
                'item' => $item,
                'count' => $count,
                'name' => $item->addon_name ?: $item->name,
                'quantity' => (float) ($count * (int) $item->addon_quantity),
                'unit' => (string) $item->unit,
                'unit_price' => round((float) $item->addon_price / (int) $item->addon_quantity, 4),
                'amount' => round($count * (float) $item->addon_price, 2),
            ];
        }

        return $lines;
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
            'name' => $line['name'],
            'quantity' => $line['quantity'],
            'unit' => $line['unit'],
            'unit_price' => $line['unit_price'],
            'amount' => $line['amount'],
        ], $lines);
    }
}
