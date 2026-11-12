<?php

namespace App\Services;

use App\DTOs\TableSort;
use App\Models\PurchaseOrder;
use App\Models\Supplier;
use App\Models\User;
use App\Notifications\PurchaseOrderNotification;
use App\Repositories\PurchaseOrderRepository;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * Purchase orders: a shop orders supplies from one of its own suppliers, the supplier confirms or
 * declines in the supplier portal, and the shop marks the delivery received, which adds the stock
 * and records one expense at the supplier's prices.
 */
class PurchaseOrderService
{
    public function __construct(
        private readonly PurchaseOrderRepository $orders,
        private readonly SupplierService $suppliers,
        private readonly InventoryService $inventory,
        private readonly ExpenseService $expenses,
    ) {}

    public function shopIdFor(User $user): int
    {
        return $this->suppliers->shopIdFor($user);
    }

    // ─── Shop ───────────────────────────────────────────────────────────────────

    public function indexData(User $user, array $filters, TableSort $sort): array
    {
        $shopId = $this->shopIdFor($user);
        $filters = [
            'search' => trim((string) ($filters['search'] ?? '')),
            'status' => in_array($filters['status'] ?? '', PurchaseOrder::STATUSES, true) ? $filters['status'] : '',
            'per_page' => (int) ($filters['per_page'] ?? 15),
        ];

        return [
            'orders' => $this->orders->paginateForShop($shopId, $filters['search'], $filters['status'], $sort, $filters['per_page']),
            'counts' => $this->orders->statusCountsForShop($shopId),
            'filters' => $filters,
        ];
    }

    /**
     * What the New order form offers: only this shop's active suppliers and what each one sells,
     * plus the shop's inventory items the stock can go into.
     */
    public function formData(User $user, ?string $supplierPublicId = null): array
    {
        $shopId = $this->shopIdFor($user);

        return [
            'suppliers' => $this->orders->catalogForShop($shopId)->map(fn (Supplier $supplier) => [
                'public_id' => $supplier->public_id,
                'name' => $supplier->name,
                'lead_time_days' => $supplier->lead_time_days,
                'items' => $supplier->items->map(fn ($item) => [
                    'public_id' => $item->public_id,
                    'name' => $item->name,
                    'unit' => $item->unit,
                    'unit_price' => (float) $item->unit_price,
                    'stock_quantity' => (float) $item->stock_quantity,
                ])->values(),
            ])->values(),
            'inventory' => $this->inventory->all($shopId)->map(fn ($item) => [
                'id' => $item->id,
                'name' => $item->name,
                'unit' => $item->unit,
                'package_unit' => $item->package_unit,
                'package_size' => $item->package_size ? (int) $item->package_size : null,
                'supplier_id' => $item->supplier_id,
            ])->values(),
            'selectedSupplier' => $supplierPublicId,
        ];
    }

    public function find(User $user, string $publicId): PurchaseOrder
    {
        return $this->orders->findForShopOrFail($publicId, $this->shopIdFor($user));
    }

    /**
     * Saves a new order as a draft, or sends it straight away. Prices come from the supplier's own
     * list, never from the browser, and every item must be one this supplier sells.
     *
     * @param  array{supplier: string, notes?: string|null, send: bool, items: list<array{supplier_item: string, quantity: mixed, inventory_id?: int|null, stock_per_unit?: int|null}>}  $data
     */
    public function create(User $user, array $data): PurchaseOrder
    {
        $shopId = $this->shopIdFor($user);
        $supplier = $this->orders->activeSupplierForShopOrFail($data['supplier'], $shopId);
        $catalog = $this->orders->supplierItems($supplier->id, array_column($data['items'], 'supplier_item'));

        $lines = [];
        $total = 0.0;
        foreach ($data['items'] as $index => $line) {
            $item = $catalog->get($line['supplier_item'])
                ?? throw ValidationException::withMessages(["items.{$index}.supplier_item" => 'Choose a supply this supplier sells.']);

            $inventoryId = $line['inventory_id'] ?? null;
            if ($inventoryId !== null && ! $this->inventory->find((int) $inventoryId, $shopId)) {
                throw ValidationException::withMessages(["items.{$index}.inventory_id" => 'Choose one of your inventory items.']);
            }

            $quantity = round((float) $line['quantity'], 2);
            $lineTotal = round($quantity * (float) $item->unit_price, 2);
            $total += $lineTotal;

            $lines[] = [
                'supplier_item_id' => $item->id,
                'inventory_id' => $inventoryId,
                'name' => $item->name,
                'unit' => $item->unit,
                'quantity' => $quantity,
                'unit_price' => (float) $item->unit_price,
                'line_total' => $lineTotal,
                'stock_per_unit' => max(1, (int) ($line['stock_per_unit'] ?? 1)),
            ];
        }

        $send = (bool) $data['send'];

        $order = DB::transaction(fn () => $this->orders->createWithItems([
            'shop_id' => $shopId,
            'supplier_id' => $supplier->id,
            'status' => $send ? PurchaseOrder::STATUS_SENT : PurchaseOrder::STATUS_DRAFT,
            'total' => round($total, 2),
            'notes' => $data['notes'] ?? null,
            'created_by' => $user->id,
            'sent_at' => $send ? now() : null,
        ], $lines));

        if ($send) {
            $this->notifySupplier($order, 'sent');
        }

        return $order;
    }

    public function send(User $user, string $publicId): PurchaseOrder
    {
        $order = DB::transaction(function () use ($user, $publicId) {
            $order = $this->orders->lockForUpdate($this->find($user, $publicId));
            $this->assertStatus($order, [PurchaseOrder::STATUS_DRAFT], 'Only a draft can be sent.');

            return $this->orders->update($order, ['status' => PurchaseOrder::STATUS_SENT, 'sent_at' => now()]);
        });

        $this->notifySupplier($order, 'sent');

        return $order;
    }

    public function cancel(User $user, string $publicId): PurchaseOrder
    {
        $wasSent = false;

        $order = DB::transaction(function () use ($user, $publicId, &$wasSent) {
            $order = $this->orders->lockForUpdate($this->find($user, $publicId));
            $this->assertStatus(
                $order,
                [PurchaseOrder::STATUS_DRAFT, PurchaseOrder::STATUS_SENT, PurchaseOrder::STATUS_CONFIRMED],
                'This order can no longer be cancelled.',
            );
            $wasSent = $order->status !== PurchaseOrder::STATUS_DRAFT;

            return $this->orders->update($order, ['status' => PurchaseOrder::STATUS_CANCELLED, 'cancelled_at' => now()]);
        });

        if ($wasSent) {
            $this->notifySupplier($order, 'cancelled');
        }

        return $order;
    }

    /**
     * The delivery arrived. Each line linked to an inventory item adds its stock; the shop pays for
     * what actually came, recorded as one expense. A line can arrive short (or not at all).
     *
     * @param  array<int, mixed>  $receivedQuantities  Keyed by purchase order item id.
     */
    public function receive(User $user, string $publicId, array $receivedQuantities): PurchaseOrder
    {
        return DB::transaction(function () use ($user, $publicId, $receivedQuantities) {
            $order = $this->orders->lockForUpdate($this->find($user, $publicId));
            $this->assertStatus(
                $order,
                [PurchaseOrder::STATUS_SENT, PurchaseOrder::STATUS_CONFIRMED],
                'Only an order that was sent can be marked received.',
            );
            $order->load('items.inventory');

            $paid = 0.0;
            foreach ($order->items as $item) {
                $received = round((float) ($receivedQuantities[$item->id] ?? $item->quantity), 2);

                if ($received < 0 || $received > (float) $item->quantity) {
                    throw ValidationException::withMessages([
                        "received.{$item->id}" => "Received {$item->name} must be between 0 and ".rtrim(rtrim(number_format((float) $item->quantity, 2, '.', ''), '0'), '.').'.',
                    ]);
                }

                $this->orders->setReceivedQuantity($item, $received);
                $paid += $received * (float) $item->unit_price;

                $stock = (int) round($received * $item->stock_per_unit);
                if ($item->inventory && ! $item->inventory->trashed() && $stock > 0) {
                    $this->inventory->adjustStock(
                        $item->inventory,
                        'restock',
                        $stock,
                        $order->po_number,
                        "Received from purchase order {$order->po_number}.",
                        recordExpense: false,
                    );
                }
            }

            $this->expenses->recordPurchaseOrder($order, $paid);

            return $this->orders->update($order, [
                'status' => PurchaseOrder::STATUS_RECEIVED,
                'received_at' => now(),
                'received_by' => $user->id,
            ]);
        });
    }

    // ─── Supplier portal ────────────────────────────────────────────────────────

    /** Orders this supplier has been sent, with how many still wait for an answer. */
    public function portalData(Supplier $supplier): array
    {
        $orders = $this->orders->forSupplier($supplier->id);

        return [
            'orders' => $orders->map(fn (PurchaseOrder $order) => [
                'public_id' => $order->public_id,
                'po_number' => $order->po_number,
                'status' => $order->status,
                'total' => (float) $order->total,
                'notes' => $order->notes,
                'expected_date' => $order->expected_date?->toDateString(),
                'supplier_note' => $order->supplier_note,
                'sent_at' => $order->sent_at?->toIso8601String(),
                'items' => $order->items->map(fn ($item) => [
                    'name' => $item->name,
                    'unit' => $item->unit,
                    'quantity' => (float) $item->quantity,
                    'unit_price' => (float) $item->unit_price,
                    'line_total' => (float) $item->line_total,
                ])->values(),
            ])->values(),
            'awaiting' => $orders->where('status', PurchaseOrder::STATUS_SENT)->count(),
        ];
    }

    public function confirmBySupplier(Supplier $supplier, string $publicId, ?string $expectedDate, ?string $note): PurchaseOrder
    {
        $order = $this->answer($supplier, $publicId, [
            'status' => PurchaseOrder::STATUS_CONFIRMED,
            'confirmed_at' => now(),
            'expected_date' => $expectedDate,
            'supplier_note' => $note,
        ]);
        $this->notifyShop($order, 'confirmed');

        return $order;
    }

    public function declineBySupplier(Supplier $supplier, string $publicId, string $reason): PurchaseOrder
    {
        $order = $this->answer($supplier, $publicId, [
            'status' => PurchaseOrder::STATUS_DECLINED,
            'declined_at' => now(),
            'supplier_note' => $reason,
        ]);
        $this->notifyShop($order, 'declined');

        return $order;
    }

    private function answer(Supplier $supplier, string $publicId, array $values): PurchaseOrder
    {
        return DB::transaction(function () use ($supplier, $publicId, $values) {
            $order = $this->orders->lockForUpdate($this->orders->findForSupplierOrFail($publicId, $supplier->id));
            $this->assertStatus($order, [PurchaseOrder::STATUS_SENT], 'This order was already answered or cancelled.');

            return $this->orders->update($order, $values);
        });
    }

    // ─── Helpers ────────────────────────────────────────────────────────────────

    private function assertStatus(PurchaseOrder $order, array $allowed, string $message): void
    {
        if (! in_array($order->status, $allowed, true)) {
            throw ValidationException::withMessages(['status' => $message]);
        }
    }

    private function notifySupplier(PurchaseOrder $order, string $event): void
    {
        $order->loadMissing('supplier.user');
        $order->supplier?->user?->notify(new PurchaseOrderNotification($order, $event, '/supplier-portal#orders'));
    }

    private function notifyShop(PurchaseOrder $order, string $event): void
    {
        $order->loadMissing('shop.owner');
        $order->shop?->owner?->notify(new PurchaseOrderNotification($order, $event, "/shop/purchase-orders/{$order->public_id}"));
    }
}
