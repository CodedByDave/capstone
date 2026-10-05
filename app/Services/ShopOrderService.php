<?php

namespace App\Services;

use App\Models\Inventory;
use App\Models\ShopOrder;
use App\Models\User;
use App\Notifications\OrderNotification;
use App\Repositories\ShopOrderRepository;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class ShopOrderService
{
    public function __construct(
        protected ShopOrderRepository $repository,
        private readonly CustomerAgreementService $customerAgreementService,
    ) {}

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

    public function createOrder(array $data, User $actor, ?string $ipAddress = null, ?string $userAgent = null): ShopOrder
    {
        return DB::transaction(function () use ($data, $actor, $ipAddress, $userAgent) {
            $supplies = $data['supplies'] ?? [];
            $agreementVersion = $data['customer_agreement_version'];
            unset($data['supplies']);
            unset($data['customer_agreement_version'], $data['customer_agreement_accepted']);

            $data['order_number'] = $this->generateOrderNumber();

            $order = $this->repository->create($data);

            foreach ($supplies as $supply) {
                if (empty($supply['inventory_id'])) {
                    continue;
                }

                $item = Inventory::where('id', $supply['inventory_id'])
                    ->where('shop_id', $order->shop_id)
                    ->first();

                if (! $item) {
                    continue;
                }

                if ($item->quantity < $supply['quantity_used']) {
                    throw \Illuminate\Validation\ValidationException::withMessages([
                        'supplies' => "Not enough stock for {$item->name}. Available: {$item->quantity} {$item->unit}.",
                    ]);
                }

                // ← use attach() not create()
                $order->supplies()->attach($supply['inventory_id'], [
                    'quantity_used' => $supply['quantity_used'],
                    'unit' => $supply['unit'],
                ]);

                $item->decrement('quantity', (float) $supply['quantity_used']);
            }

            $order->load('shop');
            $this->customerAgreementService->record($order, $actor, $agreementVersion, 'staff_attestation', $ipAddress, $userAgent);

            return $order;
        });
    }

    public function createCustomerOrder(User $customer, array $data, ?string $ipAddress, ?string $userAgent): ShopOrder
    {
        return DB::transaction(function () use ($customer, $data, $ipAddress, $userAgent) {
            $shop = $this->repository->findActiveShop((int) $data['shop_id']);
            if (! $shop) {
                throw ValidationException::withMessages(['shop_id' => 'The selected shop is unavailable.']);
            }
            $service = $this->repository->findActiveServiceForShop((int) $data['service_id'], $shop->id);
            if (! $service) {
                throw ValidationException::withMessages(['service_id' => 'The selected service is unavailable.']);
            }

            $estimatedTotal = $service->pricing_model === 'per_kg'
                ? round((float) $service->price_per_kg * (float) $data['estimated_weight_kg'], 2)
                : (float) ($service->bundle_price ?? 0);

            $order = $this->repository->create([
                'shop_id' => $shop->id,
                'user_id' => $customer->id,
                'order_source' => 'online',
                'service_id' => $service->id,
                'order_number' => $this->generateOrderNumber('ONL'),
                'customer_name' => $data['customer_name'],
                'customer_phone' => $data['customer_phone'],
                'customer_address' => $data['customer_address'] ?? null,
                'special_instructions' => $data['special_instructions'] ?? null,
                'estimated_weight_kg' => $data['estimated_weight_kg'],
                'pickup_type' => $data['pickup_type'],
                'pricing_model' => $service->pricing_model,
                'price_per_kg' => $service->price_per_kg,
                'bundle_weight_kg' => $service->bundle_weight_kg,
                'bundle_price' => $service->bundle_price,
                'additional_charges' => 0,
                'discount_amount' => 0,
                'total_amount' => $estimatedTotal,
                'payment_method' => $data['payment_method'],
                'payment_status' => 'unpaid',
                'amount_paid' => 0,
                'status' => 'pending',
            ]);

            $order->setRelation('shop', $shop);
            $this->customerAgreementService->record($order, $customer, $data['customer_agreement_version'], 'online_checkbox', $ipAddress, $userAgent);
            $customer->notify(new OrderNotification($order, 'placed'));

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
                if (empty($supply['inventory_id'])) {
                    continue;
                }

                $item = Inventory::where('id', $supply['inventory_id'])
                    ->where('shop_id', $order->shop_id)
                    ->first();

                if (! $item) {
                    continue;
                }

                if ($item->quantity < $supply['quantity_used']) {
                    throw \Illuminate\Validation\ValidationException::withMessages([
                        'supplies' => "Not enough stock for {$item->name}. Available: {$item->quantity} {$item->unit}.",
                    ]);
                }

                // ← use attach() not create()
                $order->supplies()->attach($supply['inventory_id'], [
                    'quantity_used' => $supply['quantity_used'],
                    'unit' => $supply['unit'],
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

        $updated = $this->repository->update($order, $data);

        // Notify the customer if this was an online order with a linked user
        if ($updated->user_id && in_array($status, ['in_progress', 'completed'])) {
            $updated->load('shop');
            $updated->user?->notify(new OrderNotification($updated, 'status_changed'));
        }

        return $updated;
    }

    public function updatePayment(ShopOrder $order, array $data): ShopOrder
    {
        $amountPaid = $data['amount_paid'] ?? $order->amount_paid;

        $data['payment_status'] = match (true) {
            $amountPaid <= 0 => 'unpaid',
            $amountPaid < $order->total_amount => 'partial',
            default => 'paid',
        };

        if ($data['payment_status'] === 'paid') {
            $data['paid_at'] = now();
        }

        $updated = $this->repository->update($order, $data);

        // Notify the customer when payment is marked paid
        if ($updated->user_id && $data['payment_status'] === 'paid') {
            $updated->load('shop');
            $updated->user?->notify(new OrderNotification($updated, 'payment_updated'));
        }

        return $updated;
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

    private function generateOrderNumber(string $prefix = 'ORD'): string
    {
        do {
            $number = $prefix.'-'.now()->format('Ymd').'-'.strtoupper(Str::random(5));
        } while ($this->repository->findByOrderNumber($number));

        return $number;
    }
}
