<?php

namespace App\Services;

use App\Models\ShopOrder;
use App\Models\Inventory;
use App\Repositories\ShopOrderRepository;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ShopOrderService
{
    public function __construct(protected ShopOrderRepository $repository) {}

    // ─── Queries ──────────────────────────────────────────────────────────────

    public function getPaginatedOrders(int $shopId, int $perPage = 15, array $filters = [], bool $onlyTrashed = false, ?string $branch = null): LengthAwarePaginator
    {
        return $this->repository->paginateByShop($shopId, $perPage, $filters, $onlyTrashed, $branch);
    }

    public function getOrder(int $id): ?ShopOrder
    {
        return $this->repository->findById($id);
    }

    public function getStatusSummary(int $shopId, ?string $branch = null): array
    {
        return $this->repository->countByStatus($shopId, $branch);
    }

    // ─── Create ───────────────────────────────────────────────────────────────

    public function createOrder(array $data): ShopOrder
    {
        return DB::transaction(function () use ($data) {
            $supplies = $data['supplies'] ?? [];
            unset($data['supplies']);

            $data['order_number'] = $this->generateOrderNumber();

            $order = ShopOrder::create($data);

            foreach ($supplies as $supply) {
                if (empty($supply['inventory_id'])) continue;

                $item = Inventory::where('id', $supply['inventory_id'])
                    ->where('shop_id', $order->shop_id)
                    ->first();

                if (!$item) continue;

                if ($item->quantity < $supply['quantity_used']) {
                    throw \Illuminate\Validation\ValidationException::withMessages([
                        'supplies' => "Not enough stock for {$item->name}. Available: {$item->quantity} {$item->unit}.",
                    ]);
                }

                // ← use attach() not create()
                $order->supplies()->attach($supply['inventory_id'], [
                    'quantity_used' => $supply['quantity_used'],
                    'unit'          => $supply['unit'],
                ]);

                $item->decrement('quantity', (float) $supply['quantity_used']);
            }

            return $order;
        });
    }

    // ─── Update ───────────────────────────────────────────────────────────────

    public function updateOrder(ShopOrder $order, array $data): ShopOrder
    {
        return DB::transaction(function () use ($order, $data) {
            $newSupplies = $data['supplies'] ?? [];
            unset($data['supplies']);

            // Restore old supply quantities back to inventory
            foreach ($order->supplies as $oldSupply) {
                Inventory::where('id', $oldSupply->pivot->inventory_id)
                    ->where('shop_id', $order->shop_id)
                    ->increment('quantity', (float) $oldSupply->pivot->quantity_used);
            }

            // Detach all old supplies from pivot
            $order->supplies()->detach();

            // Update order fields
            $order->update($data);

            // Re-attach new supplies and deduct inventory
            foreach ($newSupplies as $supply) {
                if (empty($supply['inventory_id'])) continue;

                $item = Inventory::where('id', $supply['inventory_id'])
                    ->where('shop_id', $order->shop_id)
                    ->first();

                if (!$item) continue;

                if ($item->quantity < $supply['quantity_used']) {
                    throw \Illuminate\Validation\ValidationException::withMessages([
                        'supplies' => "Not enough stock for {$item->name}. Available: {$item->quantity} {$item->unit}.",
                    ]);
                }

                // ← use attach() not create()
                $order->supplies()->attach($supply['inventory_id'], [
                    'quantity_used' => $supply['quantity_used'],
                    'unit'          => $supply['unit'],
                ]);

                $item->decrement('quantity', (float) $supply['quantity_used']);
            }

            return $order->fresh(['service', 'supplies']);
        });
    }

    // ─── Status & Payment ─────────────────────────────────────────────────────

    public function updateStatus(ShopOrder $order, string $status): ShopOrder
    {
        $data = ['status' => $status];

        if ($status === 'completed') {
            $data['completed_at'] = now();
        }

        return $this->repository->update($order, $data);
    }

    public function updatePayment(ShopOrder $order, array $data): ShopOrder
    {
        $amountPaid = $data['amount_paid'] ?? $order->amount_paid;

        $data['payment_status'] = match (true) {
            $amountPaid <= 0                   => 'unpaid',
            $amountPaid < $order->total_amount => 'partial',
            default                            => 'paid',
        };

        if ($data['payment_status'] === 'paid') {
            $data['paid_at'] = now();
        }

        return $this->repository->update($order, $data);
    }

    // ─── Delete ───────────────────────────────────────────────────────────────

    public function deleteOrder(ShopOrder $order): void
    {
        DB::transaction(function () use ($order) {
            // Restore inventory quantities
            foreach ($order->supplies as $supply) {
                Inventory::where('id', $supply->pivot->inventory_id)
                    ->where('shop_id', $order->shop_id)
                    ->increment('quantity', (float) $supply->pivot->quantity_used);
            }

            // Detach supplies from pivot
            $order->supplies()->detach();

            // Soft delete the order
            $order->delete();
        });
    }

    // ─── Private Helpers ──────────────────────────────────────────────────────

    private function generateOrderNumber(): string
    {
        do {
            $number = 'ORD-' . now()->format('Ymd') . '-' . strtoupper(Str::random(5));
        } while ($this->repository->findByOrderNumber($number));

        return $number;
    }
}