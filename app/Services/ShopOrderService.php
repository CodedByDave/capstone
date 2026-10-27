<?php

namespace App\Services;

use App\Events\OrderWeightConfirmed;
use App\Models\Inventory;
use App\Models\ShopOrder;
use App\Models\ShopOrderAutomationSetting;
use App\Models\ShopOrderPayment;
use App\Models\User;
use App\Notifications\OrderNotification;
use App\Repositories\ShopOrderAutomationSettingRepository;
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
        private readonly InventoryService $inventory,
        private readonly ServiceSupplyUsageService $serviceSupplies,
        private readonly ShopOrderAutomationSettingRepository $automationSettings,
    ) {}

    // ─── Queries ──────────────────────────────────────────────────────────────

    public function getPaginatedOrders(int $shopId, int $perPage = 15, array $filters = [], bool $onlyTrashed = false): LengthAwarePaginator
    {
        return $this->repository->paginateByShop($shopId, $perPage, $filters, $onlyTrashed);
    }

    public function getOrder(int $id): ?ShopOrder
    {
        return $this->repository->findById($id);
    }

    public function getStatusSummary(int $shopId): array
    {
        return $this->repository->countByStatus($shopId);
    }

    /**
     * Minutes from placing an order to its estimated completion: the wait the shop set in its order
     * automation settings, or the default when it has set none.
     */
    public function defaultCompletionMinutes(int $shopId): int
    {
        return $this->automationSettings->findForShop($shopId)?->auto_complete_grace_minutes
            ?? ShopOrderAutomationSetting::DEFAULT_GRACE_MINUTES;
    }

    // ─── Create ───────────────────────────────────────────────────────────────

    public function createOrder(array $data, User $actor, ?string $ipAddress = null, ?string $userAgent = null): ShopOrder
    {
        $order = DB::transaction(function () use ($data, $actor, $ipAddress, $userAgent) {
            $supplies = $data['supplies'] ?? [];
            $agreementVersion = $data['customer_agreement_version'];
            unset($data['supplies']);
            $data = $this->withoutAlternateContactUnlessRiderInvolved($data);
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

            // The estimated completion is never typed in: it follows from the shop's automation wait.
            $data['estimated_completion_at'] = now()->addMinutes($this->defaultCompletionMinutes((int) $data['shop_id']));

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
                $this->inventory->alertIfLowStock($item->refresh());
            }

            $order->load(['shop', 'service']);
            $this->serviceSupplies->sync($order, $actor->id);
            $this->customerAgreementService->record($order, $actor, $agreementVersion, 'staff_attestation', $ipAddress, $userAgent);

            // Laundry handed in at the counter and already weighed starts right away.
            $this->startWhenReady($order);

            return $order->refresh();
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

            if ($service->isPerPiece()) {
                if (blank($data['piece_count'] ?? null)) {
                    throw ValidationException::withMessages(['piece_count' => 'Enter how many pieces you are bringing.']);
                }
            } elseif (blank($data['estimated_weight_kg'] ?? null)) {
                throw ValidationException::withMessages(['estimated_weight_kg' => 'Enter the estimated weight of your laundry.']);
            }

            $estimate = $this->billing->estimate(
                $service,
                (float) ($data['estimated_weight_kg'] ?? 0),
                isset($data['piece_count']) ? (int) $data['piece_count'] : null,
            );
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
                'customer_lat' => $data['pickup_type'] === 'pickup' ? ($data['customer_lat'] ?? null) : null,
                'customer_lng' => $data['pickup_type'] === 'pickup' ? ($data['customer_lng'] ?? null) : null,
                'special_instructions' => $data['special_instructions'] ?? null,
                'estimated_weight_kg' => $service->isPerPiece() ? null : $data['estimated_weight_kg'],
                'pickup_type' => $data['pickup_type'],
                'pricing_model' => $service->pricing_model,
                'price_per_kg' => $service->price_per_kg,
                'bundle_weight_kg' => $service->bundle_weight_kg,
                'bundle_price' => $service->bundle_price,
                'bundle_quantity' => $estimatedBundles,
                'price_per_piece' => $service->price_per_piece,
                'piece_count' => $service->isPerPiece() ? (int) $data['piece_count'] : null,
                'additional_charges' => 0,
                'discount_amount' => 0,
                'total_amount' => $estimatedTotal,
                'payment_method' => $data['payment_method'],
                'payment_status' => 'unpaid',
                'amount_paid' => 0,
                'status' => 'pending',
            ]);

            $order->setRelation('shop', $shop);
            $order->setRelation('service', $service);
            $this->serviceSupplies->sync($order, null);
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

            // The edit form never changes status; that goes through updateStatus().
            $data['status'] = $order->status;

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

            if (($data['status'] ?? null) === 'picked_up' && $order->status !== 'picked_up') {
                $this->assertCanBePickedUp($order);
                $data['picked_up_at'] = now();
            }

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
            $data = $this->withoutAlternateContactUnlessRiderInvolved($data);

            $data['pricing_model'] = $service->pricing_model;
            $data['price_per_kg'] = $service->price_per_kg;
            $data['bundle_weight_kg'] = $service->bundle_weight_kg;
            $data['bundle_price'] = $service->bundle_price;
            $data['price_per_piece'] = $service->price_per_piece;
            if (! $service->isPerPiece()) {
                $data['piece_count'] = null;
            }

            if ($service->pricing_model === 'per_bundle' && ! empty($data['actual_weight_kg'])) {
                $data['bundle_quantity'] = max(1, (int) ceil(
                    (float) $data['actual_weight_kg'] / max((float) $service->bundle_weight_kg, 0.01),
                ));
            }

            // Base price comes from the actual weight once weighed, otherwise from the estimate.
            // Additional charges and discounts apply to either, so they are added in one place.
            if ($service->isPerPiece()) {
                $baseAmount = (int) ($data['piece_count'] ?? 0) * (float) $service->price_per_piece;
            } elseif (empty($data['actual_weight_kg'])) {
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

            // The form's Not paid / Fully paid choice, applied after the order is saved; only a change counts.
            $requestedPayment = $data['payment_state'] ?? null;
            unset($data['payment_state']);
            $wasPaid = $order->payment_status === 'paid';

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

            if (($requestedPayment === 'paid' && ! $wasPaid)
                || ($requestedPayment === 'unpaid' && ($wasPaid || (float) $order->fresh()->amount_paid > 0))) {
                $this->setPaymentState($order->fresh(), $requestedPayment, $data['payment_method'] ?? null, auth()->user());
            }

            $this->serviceSupplies->sync($order->refresh()->load('service'), auth()->id());

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
                $this->inventory->alertIfLowStock($item->refresh());
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

    /** The alternate contact only matters when a rider collects or delivers the laundry, so other orders keep none. */
    private function withoutAlternateContactUnlessRiderInvolved(array $data): array
    {
        $riderInvolved = ($data['pickup_type'] ?? null) === 'pickup' || ! empty($data['deliver_to_customer']);

        if (! $riderInvolved) {
            $data['alternate_contact_name'] = null;
            $data['alternate_contact_phone'] = null;
        }

        return $data;
    }

    /**
     * Marks an order fully paid by recording what is still owed, or takes its payments back by voiding
     * them. Online payments cannot be undone here: they went through the customer's checkout.
     *
     * @param  'paid'|'unpaid'  $state
     *
     * @throws ValidationException
     */
    public function setPaymentState(ShopOrder $order, string $state, ?string $method, User $actor): void
    {
        if ($state === 'paid') {
            $balance = $order->balanceDue();

            if ($balance > 0) {
                $this->orderPayments->recordShopPayment($actor, $order, [
                    'amount' => $balance,
                    'method' => $method ?: 'cash',
                    'note' => 'Marked as fully paid from the order edit page.',
                ]);
            }

            return;
        }

        $confirmed = $this->orderPayments->history($order)
            ->filter(fn (ShopOrderPayment $payment) => $payment->isConfirmed());

        if ($confirmed->contains(fn (ShopOrderPayment $payment) => $payment->source === ShopOrderPayment::SOURCE_ONLINE)) {
            throw ValidationException::withMessages([
                'payment_state' => 'This order has an online payment, which cannot be undone here.',
            ]);
        }

        foreach ($confirmed as $payment) {
            $this->orderPayments->voidPayment($actor, $order, $payment->public_id, 'Marked as not paid from the order edit page.');
        }
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

    /**
     * A pending order starts on its own once it can: it is fully paid, or its price is final (the
     * laundry is weighed, or the service is per piece) and the laundry is at the shop. A customer who
     * pays only at the end therefore does not hold the order up.
     */
    public function startWhenReady(ShopOrder $order): void
    {
        DB::transaction(function () use ($order): void {
            $locked = $this->repository->lockForUpdate($order);

            if ($locked->status !== 'pending') {
                return;
            }

            $priceFinalAtShop = $locked->priceIsConfirmed()
                && $this->logistics->weighingBlockedReason($locked) === null;

            if ($locked->payment_status === 'paid' || $priceFinalAtShop) {
                $this->updateStatus($locked, 'in_progress');
            }
        });
    }

    /**
     * An order can be handed over only once it is completed, fully paid, and is not going out by
     * delivery (a rider hands those over).
     *
     * @throws ValidationException
     */
    private function assertCanBePickedUp(ShopOrder $order): void
    {
        $order->loadMissing('delivery');

        $reason = match (true) {
            $order->status !== 'completed' && $order->status !== 'picked_up' => 'Only a completed order can be marked as picked up.',
            $order->deliver_to_customer || $order->delivery !== null => 'This order is being delivered to the customer, so it cannot be marked as picked up.',
            $order->payment_status !== 'paid' => 'Collect the remaining balance of ₱'.number_format($order->balanceDue(), 2).' before marking this order as picked up.',
            default => null,
        };

        if ($reason !== null) {
            throw ValidationException::withMessages(['status' => $reason]);
        }
    }

    public function updateStatus(ShopOrder $order, string $status): ShopOrder
    {
        // Nothing changes, so nobody is notified twice and the completion time is not overwritten.
        if ($order->status === $status) {
            return $order;
        }

        if ($status === 'picked_up') {
            $this->assertCanBePickedUp($order);
        }

        $data = ['status' => $status];

        if ($status === 'completed') {
            $data['completed_at'] = now();
        }

        if ($status === 'picked_up') {
            $data['picked_up_at'] = now();
        }

        if ($status === 'in_progress') {
            $data['in_progress_at'] = now();
            $data['overdue_notified_at'] = null;
        }

        $previousStatus = $order->status;
        $updated = $this->repository->update($order, $data);

        // Cancelling puts the service's supplies back; reopening a cancelled order takes them again.
        if ($status === 'cancelled' || $previousStatus === 'cancelled') {
            DB::transaction(fn () => $this->serviceSupplies->sync($updated->load('service'), auth()->id()));
        }

        // Notify the customer if this was an online order with a linked user
        if ($updated->user_id && in_array($status, ['in_progress', 'completed', 'picked_up'])) {
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
            $this->serviceSupplies->release($order, auth()->id());

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
