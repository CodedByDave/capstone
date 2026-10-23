<?php

namespace App\Services;

use App\Events\OrderWeightConfirmed;
use App\Models\Inventory;
use App\Models\ShopOrder;
use App\Models\User;
use App\Notifications\OrderNotification;
use App\Repositories\ShopOrderRepository;
use Carbon\Carbon;
use Carbon\CarbonInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class ShopOrderService
{
    public function __construct(
        protected ShopOrderRepository $repository,
        private readonly CustomerAgreementService $customerAgreementService,
        private readonly OrderBillingBreakdownService $billing,
        private readonly LogisticsService $logistics,
        private readonly OrderPaymentService $orderPayments,
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
        $order = DB::transaction(function () use ($data, $actor, $ipAddress, $userAgent) {
            $supplies = $data['supplies'] ?? [];
            $agreementVersion = $data['customer_agreement_version'];
            unset($data['supplies']);
            unset($data['customer_agreement_version'], $data['customer_agreement_accepted']);

            // A pickup order is weighed only once the rider has brought the laundry to the shop.
            if (($data['pickup_type'] ?? null) === 'pickup'
                && filled($data['actual_weight_kg'] ?? null)
                && $this->logistics->shopWaitsForPickup((int) $data['shop_id'])) {
                throw ValidationException::withMessages([
                    'actual_weight_kg' => 'A pickup order is weighed after the rider brings the laundry to the shop. Leave the actual weight empty for now.',
                ]);
            }

            $data['order_number'] = $this->generateOrderNumber();

            // Money received now is recorded as a payment; the order's totals follow from it.
            $paidNow = round((float) ($data['amount_paid'] ?? 0), 2);
            if ($paidNow > round((float) $data['total_amount'], 2)) {
                throw ValidationException::withMessages([
                    'amount_paid' => 'The payment received cannot be more than the order total.',
                ]);
            }
            unset($data['amount_paid'], $data['payment_status'], $data['paid_at']);

            $order = $this->repository->create($data);
            $this->orderPayments->recordInitialPayment($actor, $order, $paidNow, $data['payment_method'] ?? null);

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
                    'unit_price' => $item->selling_price,
                ]);

                $item->decrement('quantity', (float) $supply['quantity_used']);
            }

            $order->load('shop');
            $this->customerAgreementService->record($order, $actor, $agreementVersion, 'staff_attestation', $ipAddress, $userAgent);

            return $order;
        });

        $this->logistics->dispatchPickupRun($order);

        return $order;
    }

    public function createCustomerOrder(User $customer, array $data, ?string $ipAddress, ?string $userAgent): ShopOrder
    {
        $order = DB::transaction(function () use ($customer, $data, $ipAddress, $userAgent) {
            $shop = $this->repository->findActiveShop((int) $data['shop_id']);
            if (! $shop) {
                throw ValidationException::withMessages(['shop_id' => 'The selected shop is unavailable.']);
            }
            $service = $this->repository->findActiveServiceForShop((int) $data['service_id'], $shop->id);
            if (! $service) {
                throw ValidationException::withMessages(['service_id' => 'The selected service is unavailable.']);
            }

            $estimate = $this->billing->estimate($service, (float) $data['estimated_weight_kg']);
            $estimatedBundles = $service->pricing_model === 'per_bundle'
                ? (int) $estimate['service']['quantity']
                : null;
            $estimatedTotal = $estimate['total'];

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
                'bundle_quantity' => $estimatedBundles,
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

        $this->logistics->dispatchPickupRun($order);

        return $order;
    }

    /** Why this order cannot be weighed yet (a pickup order still on its way), or null. */
    public function weighingBlockedReason(ShopOrder $order): ?string
    {
        return $this->logistics->weighingBlockedReason($order);
    }

    /** Whether new pickup orders at this shop must wait for the rider before they are weighed. */
    public function pickupOrdersAwaitReceipt(int $shopId): bool
    {
        return $this->logistics->shopWaitsForPickup($shopId);
    }

    // ─── Update ───────────────────────────────────────────────────────────────

    public function updateOrder(ShopOrder $order, array $data): ShopOrder
    {
        $previousStatus = $order->status;

        $updated = DB::transaction(function () use ($order, $data) {
            $order = $this->repository->lockForUpdate($order);

            if ($order->payment_status === 'paid'
                && array_key_exists('actual_weight_kg', $data)
                && (float) $data['actual_weight_kg'] !== (float) $order->actual_weight_kg) {
                throw ValidationException::withMessages([
                    'actual_weight_kg' => 'The actual weight cannot be changed after payment.',
                ]);
            }

            $service = $this->repository->findActiveServiceForShop(
                (int) $data['service_id'],
                $order->shop_id,
            );

            if (! $service) {
                throw ValidationException::withMessages([
                    'service_id' => 'The selected service is unavailable for this shop.',
                ]);
            }

            if (array_key_exists('actual_weight_kg', $data)
                && filled($data['actual_weight_kg'])
                && (float) $data['actual_weight_kg'] !== (float) $order->actual_weight_kg
                && ($blocked = $this->logistics->weighingBlockedReason($order, $data['pickup_type'] ?? null)) !== null) {
                throw ValidationException::withMessages(['actual_weight_kg' => $blocked]);
            }

            $previousActualWeight = $order->actual_weight_kg;

            // The edit form can complete an order too; record when, as the status button does.
            if (($data['status'] ?? null) === 'completed' && $order->status !== 'completed') {
                $data['completed_at'] = now();
            }
            if (($data['status'] ?? null) === 'in_progress' && $order->status !== 'in_progress') {
                $data['in_progress_at'] = now();
                $data['overdue_notified_at'] = null;
            }
            // A new estimate means the order can become overdue, and be reminded about, again.
            if (array_key_exists('estimated_completion_at', $data)
                && $this->estimateChanged($order->estimated_completion_at, $data['estimated_completion_at'])) {
                $data['overdue_notified_at'] = null;
            }
            $newSupplies = $data['supplies'] ?? [];
            unset($data['supplies']);

            $data['pricing_model'] = $service->pricing_model;
            $data['price_per_kg'] = $service->price_per_kg;
            $data['bundle_weight_kg'] = $service->bundle_weight_kg;
            $data['bundle_price'] = $service->bundle_price;

            if ($service->pricing_model === 'per_bundle' && ! empty($data['actual_weight_kg'])) {
                $data['bundle_quantity'] = max(1, (int) ceil(
                    (float) $data['actual_weight_kg'] / max((float) $service->bundle_weight_kg, 0.01),
                ));
            }

            // Base price comes from the actual weight once weighed, otherwise from the estimate.
            // Additional charges and discounts apply to either, so they are added in one place.
            if (empty($data['actual_weight_kg'])) {
                $baseAmount = $service->pricing_model === 'per_kg'
                    ? (float) ($data['estimated_weight_kg'] ?? 0) * (float) $service->price_per_kg
                    : max(1, (int) ceil(
                        (float) ($data['estimated_weight_kg'] ?? 0) / max((float) $service->bundle_weight_kg, 0.01),
                    )) * (float) $service->bundle_price;
            } else {
                $baseAmount = $service->pricing_model === 'per_kg'
                    ? (float) $data['actual_weight_kg'] * (float) $service->price_per_kg
                    : (float) ($data['bundle_quantity'] ?? 1) * (float) $service->bundle_price;
            }

            // A customer-requested delivery fee is owned by the delivery flow, not the edit form,
            // so it is carried over from the stored order rather than read from the request.
            $data['total_amount'] = round(max(
                0,
                $baseAmount
                    + (float) ($data['additional_charges'] ?? 0)
                    - (float) ($data['discount_amount'] ?? 0),
            ) + (float) $order->delivery_fee, 2);
            unset($data['delivery_fee']);

            // What has been paid comes only from the recorded payments; the form cannot change it.
            unset($data['amount_paid'], $data['payment_status'], $data['paid_at']);

            // Restore old supply quantities back to inventory
            foreach ($order->supplies as $oldSupply) {
                Inventory::where('id', $oldSupply->pivot->inventory_id)
                    ->where('shop_id', $order->shop_id)
                    ->increment('quantity', (float) $oldSupply->pivot->quantity_used);
            }

            // Remember what each supply was charged at before the pivot rows are rebuilt.
            $previousPrices = $order->supplies->mapWithKeys(
                fn ($supply) => [$supply->id => $supply->pivot->unit_price],
            );

            // Detach all old supplies from pivot
            $order->supplies()->detach();

            // Persist the authoritative pricing and weight values, then re-derive the payment
            // status because the total may have changed.
            $this->repository->update($order, $data);
            $this->orderPayments->sync($order);

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
                    // A supply already on the order keeps the price it was first charged at.
                    'unit_price' => $previousPrices[$item->id] ?? $item->selling_price,
                ]);

                $item->decrement('quantity', (float) $supply['quantity_used']);
            }

            $updated = $order->fresh(['service', 'supplies', 'shop', 'customer']);

            if ($updated->user_id
                && $previousActualWeight === null
                && $updated->actual_weight_kg !== null) {
                $updated->customer?->notify(new OrderNotification($updated, 'weight_confirmed'));
            }

            if ($previousActualWeight === null && $updated->actual_weight_kg !== null) {
                OrderWeightConfirmed::dispatch($updated);
            }

            return $updated;
        });

        // The edit form can complete an order or switch it to pickup/delivery, so the board follows.
        if ($updated->status === 'completed' && $previousStatus !== 'completed') {
            $this->logistics->dispatchCompletedOrder($updated);
        } else {
            $this->logistics->dispatchPickupRun($updated);
        }

        return $updated;
    }

    private function estimateChanged(?CarbonInterface $current, mixed $new): bool
    {
        $new = blank($new) ? null : Carbon::parse($new);

        if ($current === null || $new === null) {
            return $current !== $new;
        }

        return ! $current->equalTo($new);
    }

    // ─── Status ───────────────────────────────────────────────────────────────

    public function updateStatus(ShopOrder $order, string $status): ShopOrder
    {
        // Nothing changes, so nobody is notified twice and the completion time is not overwritten.
        if ($order->status === $status) {
            return $order;
        }

        $data = ['status' => $status];

        if ($status === 'completed') {
            $data['completed_at'] = now();
        }

        if ($status === 'in_progress') {
            $data['in_progress_at'] = now();
            $data['overdue_notified_at'] = null;
        }

        $updated = $this->repository->update($order, $data);

        // Notify the customer if this was an online order with a linked user
        if ($updated->user_id && in_array($status, ['in_progress', 'completed'])) {
            $updated->load('shop');
            $updated->customer?->notify(new OrderNotification($updated, 'status_changed'));
        }

        // A pickup order goes back to the customer, so it joins the delivery board.
        if ($status === 'completed') {
            $this->logistics->dispatchCompletedOrder($updated);
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
