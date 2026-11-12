<?php

namespace App\Services;

use App\Events\OrderWeightConfirmed;
use App\Exceptions\DeliveryNotAvailableException;
use App\Models\Inventory;
use App\Models\ShopOrder;
use App\Models\ShopOrderAutomationSetting;
use App\Models\ShopOrderServiceLine;
use App\Models\User;
use App\Notifications\OrderNotification;
use App\Repositories\ShopOrderAutomationSettingRepository;
use App\Repositories\ShopOrderRepository;
use App\Services\Sms\ShopSmsService;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;
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
        private readonly ExpenseService $expenses,
        private readonly ShopOrderAutomationSettingRepository $automationSettings,
        private readonly CustomerAdditionalChargeService $additionalCharges,
        private readonly OrderTaxService $tax,
        private readonly ActivityLogService $activityLog,
        private readonly RiderLoadLimitService $riderLoad,
        private readonly CounterDeliveryFeeService $counterDelivery,
        private readonly ShopSmsService $sms,
        private readonly PickupFeeService $pickupFees,
        private readonly OrderSupplyStockService $supplyStock,
    ) {}

    // ─── Queries ──────────────────────────────────────────────────────────────

    public function getPaginatedOrders(int $shopId, int $perPage = 15, array $filters = [], bool $onlyTrashed = false): LengthAwarePaginator
    {
        $orders = $this->repository->paginateByShop($shopId, $perPage, $filters, $onlyTrashed);

        if (! $onlyTrashed) {
            $this->attachQueuePositions($shopId, $orders->getCollection());
        }

        return $orders;
    }

    /**
     * Tells staff which place each order holds in the shop's queue (1 = work on it first), so the
     * laundry is done in the right order. Orders that have left the queue have no place.
     *
     * @param  \Illuminate\Support\Collection<int, ShopOrder>  $orders
     */
    private function attachQueuePositions(int $shopId, $orders): void
    {
        if ($orders->whereIn('status', ShopOrder::QUEUE_STATUSES)->isEmpty()) {
            return;
        }

        $positions = array_flip($this->repository->queueIdsForShop($shopId));

        foreach ($orders as $order) {
            $order->setAttribute(
                'queue_position',
                in_array($order->status, ShopOrder::QUEUE_STATUSES, true) && isset($positions[$order->id])
                    ? $positions[$order->id] + 1
                    : null,
            );
        }
    }

    /** @return array{waiting: int, rush: int} How many orders are in the shop's queue, and how many of them are rush. */
    public function queueSummary(int $shopId): array
    {
        $byStatus = $this->repository->countByStatus($shopId);

        return [
            'waiting' => (int) collect(ShopOrder::QUEUE_STATUSES)->sum(fn (string $status) => $byStatus[$status] ?? 0),
            'rush' => $this->repository->countRushInQueue($shopId),
        ];
    }

    /** The flat rush fee the shop charges, or 0 when it does not offer rush. */
    public function rushFeeFor(int $shopId): float
    {
        return $this->repository->rushFeeForShop($shopId);
    }

    /** @return array{min: float|null, max: float|null} The weight range a rider run can carry for this shop. */
    public function riderLoadLimitFor(int $shopId): array
    {
        return $this->riderLoad->limitsFor($this->repository->shopWithRiderLoadLimit($shopId));
    }

    /**
     * Moves an order that is still in the queue to the front of the shop's queue for the shop's flat rush fee, or takes
     * it back out of the rush lane. The fee is added to (or removed from) the order's total. Only an
     * unpaid order can change, so a settled total is never reopened.
     */
    public function setRush(ShopOrder $order, bool $rush, User $actor): ShopOrder
    {
        return DB::transaction(function () use ($order, $rush, $actor): ShopOrder {
            $locked = $this->repository->lockForUpdate($order);

            if ($locked->is_rush === $rush) {
                return $locked;
            }

            if (! in_array($locked->status, ShopOrder::QUEUE_STATUSES, true)) {
                throw ValidationException::withMessages([
                    'rush' => 'Only an order that is still in the queue can be rushed. Completed orders cannot.',
                ]);
            }

            // Rush is decided before the customer pays, so a paid total is never reopened for a rush fee.
            if ((float) $locked->amount_paid > 0) {
                throw ValidationException::withMessages([
                    'rush' => $rush
                        ? 'Only an unpaid order can be rushed. Choose rush before the customer pays.'
                        : 'The rush fee cannot be removed once a payment has been recorded on the order.',
                ]);
            }

            $fee = $rush ? $this->repository->rushFeeForShop((int) $locked->shop_id) : 0.0;

            if ($rush && $fee <= 0) {
                throw ValidationException::withMessages([
                    'rush' => 'Set a rush fee in Settings before rushing an order.',
                ]);
            }

            $delta = $rush ? $fee : -(float) $locked->rush_fee;
            // Rider fees sit outside tax, so they are taken out and added back unchanged.
            $delivery = (float) $locked->delivery_fee + (float) $locked->pickup_fee;
            $subtotal = round(max(0, (float) $locked->total_amount - (float) $locked->tax_amount - $delivery + $delta), 2);
            $taxAmount = $this->tax->amountOn($subtotal, (float) $locked->tax_rate);

            $updated = $this->repository->update($locked, [
                'is_rush' => $rush,
                'rush_fee' => $fee,
                'rush_marked_at' => $rush ? now() : null,
                'tax_amount' => $taxAmount,
                'total_amount' => round($subtotal + $taxAmount + $delivery, 2),
            ]);

            // The total changed, so the payment status follows the recorded payments.
            $updated = $this->orderPayments->sync($updated);

            $this->activityLog->log(
                $updated,
                $rush ? 'Marked order as rush' : 'Removed rush from order',
                ['rush_fee' => $fee, 'by' => $actor->id],
                (int) $updated->shop_id,
                'Operations',
            );

            if ($updated->user_id) {
                $updated->load('shop');
                $updated->customer?->notify(new OrderNotification($updated, $rush ? 'rush_marked' : 'rush_removed'));
            }

            return $updated;
        });
    }

    public function getOrder(int $id): ?ShopOrder
    {
        return $this->repository->findById($id);
    }

    /** Orders placed today, i.e. the "New" orders for the shop. */
    public function newOrdersToday(int $shopId): int
    {
        return $this->repository->countCreatedSince($shopId, now()->startOfDay());
    }

    /** @return array{operations_orders: int}|null Sidebar badge counts for a shop owner or staff member. */
    public function sidebarBadgesFor(User $user): ?array
    {
        if (! in_array($user->role, ['owner', 'staff'], true)) {
            return null;
        }

        $shopId = $this->repository->shopIdForUser($user);

        return $shopId ? ['operations_orders' => $this->newOrdersToday($shopId)] : null;
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

    /** The tax rate and label the order form shows for this shop (from its BIR registration). */
    public function taxFor(int $shopId): array
    {
        return $this->tax->forShop($shopId);
    }

    // ─── Create ───────────────────────────────────────────────────────────────

    public function createOrder(array $data, User $actor, ?string $ipAddress = null, ?string $userAgent = null): ShopOrder
    {
        // Worked out before the transaction: per-km pricing measures the route, a network call.
        $data = $this->withCounterDeliveryFee($data);

        $order = DB::transaction(function () use ($data, $actor, $ipAddress, $userAgent) {
            $supplies = $data['supplies'] ?? [];
            $agreementVersion = $data['customer_agreement_version'];
            $requestedLines = $data['service_lines'] ?? [];
            unset($data['supplies'], $data['service_lines']);
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

            // Extra services are priced here from the shop's own rates, never from the browser.
            $serviceLines = $this->buildServiceLines($requestedLines, (int) $data['shop_id'], ($data['pickup_type'] ?? null) === 'pickup');

            // A rider can only take laundry within the shop's load limit.
            $this->ensureRiderCanCarry($data, $serviceLines);
            if ($serviceLines !== []) {
                $data['total_amount'] = round(max(
                    0,
                    $this->mainServiceAmount($data)
                        + array_sum(array_column($serviceLines, 'amount'))
                        + (float) ($data['additional_charges'] ?? 0)
                        - (float) ($data['discount_amount'] ?? 0),
                ), 2);
            }

            // A walk-in can ask for rush at the counter. The fee is the shop's own, never the browser's.
            $isRush = (bool) ($data['is_rush'] ?? false);
            unset($data['is_rush']);
            $rushFee = 0.0;
            if ($isRush) {
                $rushFee = $this->repository->rushFeeForShop((int) $data['shop_id']);
                if ($rushFee <= 0) {
                    throw ValidationException::withMessages([
                        'is_rush' => 'Set a rush fee in Settings before rushing an order.',
                    ]);
                }
                $data['is_rush'] = true;
                $data['rush_fee'] = $rushFee;
                $data['rush_marked_at'] = now();
            }

            // The form's total is the amount before tax; the shop's tax is added here, never in the browser.
            $subtotal = round((float) $data['total_amount'] + $rushFee, 2);
            $rate = $this->tax->rateForShop((int) $data['shop_id']);
            $tax = $this->tax->amountOn($subtotal, $rate);
            $data['tax_rate'] = $rate;
            $data['tax_amount'] = $tax;
            // The delivery fee is added after tax, the same as on orders placed in the app.
            $data['total_amount'] = round($subtotal + $tax + (float) $data['delivery_fee'], 2);

            // Money received now is recorded as a payment; the order's totals follow from it.
            $paidNow = round((float) ($data['amount_paid'] ?? 0), 2);
            if ($paidNow > round((float) $data['total_amount'], 2)) {
                throw ValidationException::withMessages([
                    'amount_paid' => 'The payment received cannot be more than the order total.',
                ]);
            }

            // Cash handed over covers the payment; anything above it goes back to the customer as change.
            // Only cash is handed over, so GCash and Maya payments have no cash received or change.
            $method = $paidNow > 0 ? ($data['payment_method'] ?? 'cash') : null;
            $data['payment_method'] = $method;
            if ($method !== 'cash') {
                $data['amount_tendered'] = null;
            }
            $tendered = filled($data['amount_tendered'] ?? null) ? round((float) $data['amount_tendered'], 2) : null;
            if ($tendered !== null && $paidNow > 0 && $tendered < $paidNow) {
                throw ValidationException::withMessages([
                    'amount_tendered' => 'The cash received is less than the payment of ₱'.number_format($paidNow, 2).'.',
                ]);
            }
            unset($data['amount_paid'], $data['amount_tendered'], $data['payment_status'], $data['paid_at']);

            $order = $this->repository->create($data);
            $this->repository->replaceServiceLines($order, $serviceLines);
            $this->orderPayments->recordInitialPayment(
                $actor,
                $order,
                $paidNow,
                $method,
                $paidNow > 0 ? $tendered : null,
            );

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
        // A rider pickup is charged with the shop's delivery pricing. It is worked out before the
        // transaction because a per-kilometer fee measures the road distance first.
        $pickupFee = 0.0;
        if ($data['pickup_type'] === 'pickup') {
            try {
                $pickupFee = $this->pickupFees->feeFor(
                    (int) $data['shop_id'],
                    isset($data['customer_lat']) ? (float) $data['customer_lat'] : null,
                    isset($data['customer_lng']) ? (float) $data['customer_lng'] : null,
                );
            } catch (DeliveryNotAvailableException $exception) {
                throw ValidationException::withMessages(['customer_address' => $exception->getMessage()]);
            }
        }

        $order = DB::transaction(function () use ($customer, $data, $ipAddress, $userAgent, $pickupFee) {
            $shop = $this->repository->findActiveShop((int) $data['shop_id']);
            if (! $shop) {
                throw ValidationException::withMessages(['shop_id' => 'The selected shop is unavailable.']);
            }
            if (! $shop->isOpenAt()) {
                throw ValidationException::withMessages(['shop_id' => $shop->shop_name.' is closed right now, so it cannot take new orders. Check its opening days and hours.']);
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

            // A rider only collects laundry within the shop's load limit; heavier or lighter loads are dropped off.
            if ($data['pickup_type'] === 'pickup' && ($tooHeavy = $this->riderLoad->violation(
                $shop,
                $this->riderLoad->weightFromParts(
                    (string) $service->pricing_model,
                    null,
                    filled($data['estimated_weight_kg'] ?? null) ? (float) $data['estimated_weight_kg'] : null,
                ),
                RiderLoadLimitService::PURPOSE_PICKUP,
            )) !== null) {
                throw ValidationException::withMessages(['pickup_type' => $tooHeavy]);
            }

            // The add-ons and retail products the customer picked, priced from the shop's own items.
            $additionalLines = [
                ...$this->additionalCharges->resolve($shop->id, $data['additional_items'] ?? [], lock: true),
                ...$this->additionalCharges->resolveRetail($shop->id, $data['retail_items'] ?? [], lock: true),
            ];

            $estimate = $this->billing->estimate(
                $service,
                (float) ($data['estimated_weight_kg'] ?? 0),
                isset($data['piece_count']) ? (int) $data['piece_count'] : null,
                filled($data['promotion_id'] ?? null) ? (int) $data['promotion_id'] : null,
                $additionalLines,
            );
            $estimatedBundles = $service->pricing_model === 'per_bundle'
                ? (int) $estimate['service']['quantity']
                : null;
            // The pickup fee is added after tax, the same as a delivery fee.
            $estimatedTotal = round($estimate['total'] + $pickupFee, 2);

            $order = $this->repository->create([
                'shop_id' => $shop->id,
                'user_id' => $customer->id,
                'order_source' => 'online',
                'service_id' => $service->id,
                'order_number' => $this->generateOrderNumber('ONL'),
                'customer_name' => $data['customer_name'],
                'customer_phone' => $data['customer_phone'],
                'customer_address' => $data['customer_address'] ?? null,
                'alternate_contact_name' => $data['pickup_type'] === 'pickup' ? ($data['alternate_contact_name'] ?? null) : null,
                'alternate_contact_phone' => $data['pickup_type'] === 'pickup' ? ($data['alternate_contact_phone'] ?? null) : null,
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
                'additional_charges' => $estimate['additional_charges'],
                'promotion_id' => filled($data['promotion_id'] ?? null) ? (int) $data['promotion_id'] : null,
                'discount_amount' => $estimate['discount'],
                'tax_rate' => $estimate['tax_rate'],
                'tax_amount' => $estimate['tax'],
                'pickup_fee' => $pickupFee,
                'total_amount' => $estimatedTotal,
                // Customers choose how to pay once the actual weight is confirmed; until then it stays cash.
                'payment_method' => $data['payment_method'] ?? 'cash',
                'payment_status' => 'unpaid',
                'amount_paid' => 0,
                'status' => 'pending',
            ]);

            // The picked items are put aside from stock now, as they are when the shop adds them to an order.
            $this->additionalCharges->attachToOrder($order, $additionalLines);

            $order->setRelation('shop', $shop);
            $order->setRelation('service', $service);
            $this->serviceSupplies->sync($order, null);
            $this->customerAgreementService->record($order, $customer, $data['customer_agreement_version'], 'online_checkbox', $ipAddress, $userAgent);
            $customer->notify(new OrderNotification($order, 'placed'));

            return $order;
        });

        $this->logistics->dispatchPickupRun($order);
        $this->notifyShopOfOnlineOrder($order);

        return $order;
    }

    /** Tells the owner and the staff who can view Operations that a customer placed an order online. */
    private function notifyShopOfOnlineOrder(ShopOrder $order): void
    {
        $shop = $order->shop ?? $order->load('shop')->shop;

        Notification::send(
            $this->repository->onlineOrderRecipients($shop),
            new OrderNotification($order, 'new_online_order'),
        );
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

    /**
     * Saves what may still change once an order is placed: where the rider goes (address and
     * alternate contact, only when a rider is involved) and the first weigh-in of laundry that had
     * no weight yet. The customer, fulfillment, service, pricing, payment and supplies are locked;
     * any of them in $data is ignored. Status and payments have their own actions.
     *
     * @throws ValidationException
     */
    public function updateOrder(ShopOrder $order, array $data): ShopOrder
    {
        // A products order is priced when placed and has nothing to weigh or deliver.
        if ($order->isProductOrder()) {
            throw ValidationException::withMessages(['order' => 'A products order cannot be edited. Use the actions on the order page.']);
        }

        $updated = DB::transaction(function () use ($order, $data) {
            $order = $this->repository->lockForUpdate($order);
            $changes = [];

            if ($order->pickup_type === 'pickup' || $order->deliver_to_customer) {
                foreach (['customer_address', 'alternate_contact_name', 'alternate_contact_phone'] as $field) {
                    if (array_key_exists($field, $data)) {
                        $changes[$field] = $data[$field];
                    }
                }
            }

            $weight = $this->firstWeighIn($order, $data);
            $lineWeights = $this->firstLineWeighIns($order, $data['service_lines'] ?? []);
            $weighed = $weight !== null || $lineWeights !== [];

            if ($weighed && ($blocked = $this->logistics->weighingBlockedReason($order)) !== null) {
                throw ValidationException::withMessages([
                    $weight !== null ? 'actual_weight_kg' : 'service_lines' => $blocked,
                ]);
            }

            $weightNeedsApproval = false;
            $oldProofPath = null;
            $newProofPath = null;

            // Every weigh-in, of the main service or an additional one, follows the same rules.
            if ($weighed) {
                $field = $weight !== null ? 'actual_weight_kg' : 'service_lines';

                if (in_array($order->status, ['cancelled', 'picked_up'], true)) {
                    throw ValidationException::withMessages([
                        $field => 'This order is closed, so its weight can no longer be recorded.',
                    ]);
                }

                if ($order->payment_status === 'paid') {
                    throw ValidationException::withMessages([
                        $field => 'The weight cannot be recorded after the order is paid.',
                    ]);
                }

                if ($weight !== null) {
                    $changes['actual_weight_kg'] = $weight;
                    if ($order->pricing_model === 'per_bundle') {
                        $changes['bundle_quantity'] = $this->bundlesFor($weight, (float) $order->bundle_weight_kg);
                    }
                }

                // An online customer approves the weight the shop entered, so it needs a photo of the scale.
                if ($order->order_source === 'online') {
                    $proof = $data['weight_proof'] ?? null;
                    if (! $proof instanceof UploadedFile) {
                        throw ValidationException::withMessages([
                            'weight_proof' => 'Attach a photo of the laundry on the scale so the customer can verify the weight.',
                        ]);
                    }

                    $weightNeedsApproval = true;
                    $newProofPath = $proof->store("weight-proofs/{$order->shop_id}/{$order->payment_public_id}", 'private');
                    $oldProofPath = $order->weight_proof_path;
                    $changes['weight_proof_path'] = $newProofPath;
                    $changes['weight_approval_status'] = 'pending';
                    $changes['weight_proof_uploaded_at'] = now();
                    $changes['weight_decided_at'] = null;
                }
            }

            if ($weighed) {
                $lineAmounts = 0.0;
                foreach ($order->serviceLines as $index => $line) {
                    if (array_key_exists($index, $lineWeights)) {
                        $lineChanges = $this->weighedLine($line, $lineWeights[$index]);
                        $this->repository->updateServiceLine($line, $lineChanges);
                        $lineAmounts += $lineChanges['amount'];
                    } else {
                        $lineAmounts += (float) $line->amount;
                    }
                }

                // Priced from the rates stored on the order, so a later price change never reprices it.
                $subtotal = round(max(
                    0,
                    $this->storedBaseAmount($order, $changes) + $lineAmounts
                        + (float) $order->additional_charges
                        + (float) $order->rush_fee
                        - (float) $order->discount_amount,
                ), 2);
                $changes['tax_amount'] = $this->tax->amountOn($subtotal, (float) $order->tax_rate);
                $changes['total_amount'] = round(
                    $subtotal + $changes['tax_amount'] + (float) $order->delivery_fee + (float) $order->pickup_fee,
                    2,
                );
            }

            if ($changes !== []) {
                $this->repository->update($order, $changes);
            }

            if ($weighed) {
                // The total changed, so the payment status follows the recorded payments.
                $this->orderPayments->sync($order);
            }

            if ($weight !== null) {
                $this->serviceSupplies->sync($order->refresh()->load('service'), auth()->id());
            }

            $updated = $order->fresh(['service', 'serviceLines', 'supplies', 'shop', 'customer']);

            if ($oldProofPath !== null && $oldProofPath !== $newProofPath) {
                Storage::disk('private')->delete($oldProofPath);
            }

            if ($weighed) {
                if ($updated->user_id) {
                    $updated->customer?->notify(new OrderNotification($updated, 'weight_confirmed'));
                }

                // An online order's weight only counts once the customer approves it (see OrderWeightApprovalService).
                if (! $weightNeedsApproval && ! $updated->awaitingWeightApproval()) {
                    OrderWeightConfirmed::dispatch($updated);
                }
            }

            return $updated;
        });

        // A new address can change where a pending pickup run goes.
        $this->logistics->dispatchPickupRun($updated);

        return $updated;
    }

    /** The weight to record when this order's main service is weighed for the first time, or null. */
    private function firstWeighIn(ShopOrder $order, array $data): ?float
    {
        if ($order->pricing_model === 'per_piece'
            || $order->actual_weight_kg !== null
            || ! filled($data['actual_weight_kg'] ?? null)) {
            return null;
        }

        return (float) $data['actual_weight_kg'];
    }

    /**
     * The weights to record for additional services that are weighed for the first time, keyed by the
     * line's position on the order (the edit form sends the lines in the same order).
     *
     * @param  array<int, array<string, mixed>>  $requested
     * @return array<int, float>
     */
    private function firstLineWeighIns(ShopOrder $order, array $requested): array
    {
        $requested = array_values($requested);
        $weights = [];

        foreach ($order->serviceLines as $index => $line) {
            $weight = $requested[$index]['weight_kg'] ?? null;

            if ($line->pricing_model !== 'per_piece' && $line->weight_kg === null && filled($weight)) {
                $weights[$index] = (float) $weight;
            }
        }

        return $weights;
    }

    /** The weight, bundles and amount of an additional service line once it is weighed, at its stored rates. */
    private function weighedLine(ShopOrderServiceLine $line, float $weight): array
    {
        if ($line->pricing_model === 'per_bundle') {
            $bundles = $this->bundlesFor($weight, (float) $line->bundle_weight_kg);

            return ['weight_kg' => $weight, 'bundle_quantity' => $bundles, 'amount' => round($bundles * (float) $line->bundle_price, 2)];
        }

        return ['weight_kg' => $weight, 'bundle_quantity' => null, 'amount' => round($weight * (float) $line->price_per_kg, 2)];
    }

    /** The main service amount at the order's stored rates; before weighing it follows the customer's estimate. */
    private function storedBaseAmount(ShopOrder $order, array $changes): float
    {
        $weight = $changes['actual_weight_kg'] ?? $order->actual_weight_kg;
        $weighed = $weight !== null;
        $weight = (float) ($weight ?? $order->estimated_weight_kg ?? 0);

        return match ($order->pricing_model) {
            'per_piece' => (int) $order->piece_count * (float) $order->price_per_piece,
            'per_bundle' => ($weighed
                ? (int) ($changes['bundle_quantity'] ?? $order->bundle_quantity ?? 1)
                : $this->bundlesFor($weight, (float) $order->bundle_weight_kg)) * (float) $order->bundle_price,
            default => $weight * (float) $order->price_per_kg,
        };
    }

    private function bundlesFor(float $weight, float $bundleWeight): int
    {
        return max(1, (int) ceil($weight / max($bundleWeight, 0.01)));
    }

    /**
     * Prices the additional service lines of an order from the shop's own services.
     *
     * @param  array<int, array<string, mixed>>  $lines  service_id plus weight_kg or piece_count per line.
     * @return list<array<string, mixed>> Rows ready to store, each with its copy of the rate and its amount.
     *
     * @throws ValidationException When a service is not one of the shop's active services.
     */
    private function buildServiceLines(array $lines, int $shopId, bool $weighedLater): array
    {
        $built = [];

        foreach (array_values($lines) as $index => $line) {
            $service = $this->repository->findActiveServiceForShop((int) ($line['service_id'] ?? 0), $shopId);

            if (! $service) {
                throw ValidationException::withMessages([
                    "service_lines.$index.service_id" => 'The selected service is unavailable for this shop.',
                ]);
            }

            $weight = filled($line['weight_kg'] ?? null) ? (float) $line['weight_kg'] : null;
            $pieces = filled($line['piece_count'] ?? null) ? (int) $line['piece_count'] : null;
            $bundles = null;

            if ($service->isPerPiece()) {
                if ($pieces === null) {
                    throw ValidationException::withMessages(["service_lines.$index.piece_count" => 'Enter how many pieces.']);
                }
                $weight = null;
                $amount = $pieces * (float) $service->price_per_piece;
            } else {
                if ($weight === null && ! $weighedLater) {
                    throw ValidationException::withMessages(["service_lines.$index.weight_kg" => 'Enter the weight in kg.']);
                }
                $pieces = null;
                if ($service->isPerBundle() && $weight !== null) {
                    $bundles = max(1, (int) ceil($weight / max((float) $service->bundle_weight_kg, 0.01)));
                }
                $amount = match (true) {
                    $weight === null => 0.0,
                    $service->isPerBundle() => $bundles * (float) $service->bundle_price,
                    default => $weight * (float) $service->price_per_kg,
                };
            }

            $built[] = [
                'service_id' => $service->id,
                'service_name' => $service->service_name,
                'pricing_model' => $service->pricing_model,
                'price_per_kg' => $service->price_per_kg,
                'bundle_weight_kg' => $service->bundle_weight_kg,
                'bundle_price' => $service->bundle_price,
                'price_per_piece' => $service->price_per_piece,
                'weight_kg' => $weight,
                'bundle_quantity' => $bundles,
                'piece_count' => $pieces,
                'amount' => round($amount, 2),
            ];
        }

        return $built;
    }

    /**
     * A staff-created order with a rider leg (collected by a rider, or delivered back) must fit the
     * shop's rider load limit. Only the weight known now is checked; a pickup weighed later is checked
     * again when its delivery is dispatched.
     *
     * @param  array<int, array<string, mixed>>  $serviceLines
     */
    private function ensureRiderCanCarry(array $data, array $serviceLines): void
    {
        $pickup = ($data['pickup_type'] ?? null) === 'pickup';
        if (! $pickup && empty($data['deliver_to_customer'])) {
            return;
        }

        $weight = $this->riderLoad->weightFromParts(
            (string) ($data['pricing_model'] ?? 'per_kg'),
            filled($data['actual_weight_kg'] ?? null) ? (float) $data['actual_weight_kg'] : null,
            filled($data['estimated_weight_kg'] ?? null) ? (float) $data['estimated_weight_kg'] : null,
            $serviceLines,
        );

        $reason = $this->riderLoad->violation(
            $this->repository->shopWithRiderLoadLimit((int) $data['shop_id']),
            $weight,
            $pickup ? RiderLoadLimitService::PURPOSE_PICKUP : RiderLoadLimitService::PURPOSE_DELIVERY,
        );

        if ($reason !== null) {
            throw ValidationException::withMessages([$pickup ? 'pickup_type' : 'deliver_to_customer' => $reason]);
        }
    }

    /** The order's main service amount from the rate and quantity fields of a new order. */
    private function mainServiceAmount(array $data): float
    {
        return match ($data['pricing_model'] ?? 'per_kg') {
            'per_piece' => (int) ($data['piece_count'] ?? 0) * (float) ($data['price_per_piece'] ?? 0),
            'per_bundle' => (float) ($data['bundle_quantity'] ?? 1) * (float) ($data['bundle_price'] ?? 0),
            default => (float) ($data['actual_weight_kg'] ?? 0) * (float) ($data['price_per_kg'] ?? 0),
        };
    }

    /** The alternate contact only matters when a rider collects or delivers the laundry, so other orders keep none. */
    /**
     * Sets the delivery fee of an order taken at the counter. Only an order a rider brings back to
     * the customer has one; its pinned location is kept so the rider can find the customer.
     */
    private function withCounterDeliveryFee(array $data): array
    {
        $lat = isset($data['customer_lat']) ? (float) $data['customer_lat'] : null;
        $lng = isset($data['customer_lng']) ? (float) $data['customer_lng'] : null;
        unset($data['customer_lat'], $data['customer_lng']);
        $data['delivery_fee'] = 0;

        if (empty($data['deliver_to_customer'])) {
            return $data;
        }

        try {
            $quote = $this->counterDelivery->quote((int) $data['shop_id'], $lat, $lng);
        } catch (DeliveryNotAvailableException $exception) {
            throw ValidationException::withMessages(['customer_lat' => $exception->getMessage()]);
        }

        return [
            ...$data,
            'delivery_fee' => $quote['fee'],
            'customer_lat' => $lat,
            'customer_lng' => $lng,
        ];
    }

    private function withoutAlternateContactUnlessRiderInvolved(array $data): array
    {
        $riderInvolved = ($data['pickup_type'] ?? null) === 'pickup' || ! empty($data['deliver_to_customer']);

        if (! $riderInvolved) {
            $data['alternate_contact_name'] = null;
            $data['alternate_contact_phone'] = null;
        }

        return $data;
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

    /**
     * A status change made by hand. Completing an order early (or when it is overdue and the
     * automation has not done it) is allowed only for an order being worked on whose price is final,
     * so the customer is never told to pay an amount that can still change.
     *
     * @throws ValidationException
     */
    public function changeStatusByStaff(ShopOrder $order, string $status): ShopOrder
    {
        if ($status === 'completed' && $order->status !== 'completed') {
            // Products only need to be put together, so a products order can be marked ready straight away.
            $readyToComplete = $order->status === 'in_progress'
                || ($order->isProductOrder() && $order->status === 'pending');

            if (! $readyToComplete) {
                throw ValidationException::withMessages([
                    'status' => 'Only an order that is in progress can be marked as completed.',
                ]);
            }

            if (! $order->priceIsConfirmed()) {
                throw ValidationException::withMessages([
                    'status' => 'Enter the actual weight on the Edit page before completing this order.',
                ]);
            }
        }

        if ($status === 'in_progress' && $order->status === 'completed') {
            return $this->reopenCompletedOrder($order);
        }

        return $this->updateStatus($order, $status);
    }

    /**
     * Undoes a completion made by mistake: the order goes back to in progress, loses its completion
     * time, and the return delivery created for it is withdrawn unless a rider already has the laundry.
     *
     * @throws ValidationException
     */
    private function reopenCompletedOrder(ShopOrder $order): ShopOrder
    {
        return DB::transaction(function () use ($order): ShopOrder {
            $locked = $this->repository->lockForUpdate($order);

            if ($locked->status !== 'completed') {
                throw ValidationException::withMessages([
                    'status' => 'Only a completed order can be reverted to in progress.',
                ]);
            }

            $this->logistics->withdrawDeliveryForReopenedOrder($locked);
            $this->repository->update($locked, ['completed_at' => null]);

            return $this->updateStatus($locked->refresh(), 'in_progress');
        });
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

        // Laundry is not worked on until the customer has approved the weight the shop recorded.
        if ($status === 'in_progress' && $order->awaitingWeightApproval()) {
            throw ValidationException::withMessages([
                'status' => 'The customer must approve the recorded weight before this order can start.',
            ]);
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

        $updated = DB::transaction(function () use ($order, $data, $status, $previousStatus): ShopOrder {
            // Cancelling and reopening move stock, so two requests at once (the customer and staff
            // cancelling together) must not both move it.
            if ($status === 'cancelled' || $previousStatus === 'cancelled') {
                $order = $this->repository->lockForUpdate($order);

                if ($order->status !== $previousStatus) {
                    throw ValidationException::withMessages([
                        'status' => 'This order was just updated by someone else. Refresh the page and try again.',
                    ]);
                }
            }

            // Reopening a cancelled order takes its add-ons and products from stock again, before
            // anything changes. An order cancelled before stock was returned on cancel never gave
            // them back, so it has nothing to take.
            if ($previousStatus === 'cancelled' && $order->supplies_returned_at !== null) {
                $this->supplyStock->takeFromStock(
                    $order,
                    auth()->id(),
                    "Taken again by reopened order {$order->order_number}",
                    "Order {$order->order_number} cannot be reopened: ",
                );
                $data['supplies_returned_at'] = null;
            }

            // A cancelled order puts its add-ons and products back on the shelf.
            if ($status === 'cancelled') {
                $data['supplies_returned_at'] = now();
            }

            $updated = $this->repository->update($order, $data);

            if ($status === 'cancelled') {
                $this->supplyStock->returnToStock($updated, auth()->id(), "Returned from cancelled order {$updated->order_number}");
            }

            return $updated;
        });

        // Cancelling puts the service's supplies back; reopening a cancelled order takes them again.
        if ($status === 'cancelled' || $previousStatus === 'cancelled') {
            DB::transaction(function () use ($updated) {
                $this->serviceSupplies->sync($updated->load('service'), auth()->id());
                $this->expenses->syncOrderRefund($updated);
            });
        }

        // Notify the customer if this was an online order with a linked user
        if ($updated->user_id && in_array($status, ['in_progress', 'completed', 'picked_up'])) {
            $updated->load('shop');
            $updated->customer?->notify(new OrderNotification($updated, 'status_changed'));
        }

        // A pickup order goes back to the customer, so it joins the delivery board.
        if ($status === 'completed') {
            $this->logistics->dispatchCompletedOrder($updated);

            // A customer collecting at the shop is texted from the shop's phone, linked account or not.
            if ($this->sms->shouldAutoNotifyReady($updated)) {
                $this->sms->queueReadyNotice($updated);
            }
        }

        return $updated;
    }

    // Archiving and restoring orders lives in OrderArchiveService.

    // ─── Private Helpers ──────────────────────────────────────────────────────

    private function generateOrderNumber(string $prefix = 'ORD'): string
    {
        do {
            $number = $prefix.'-'.now()->format('Ymd').'-'.strtoupper(Str::random(5));
        } while ($this->repository->findByOrderNumber($number));

        return $number;
    }
}
