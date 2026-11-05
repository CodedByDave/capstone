<?php

namespace App\Services;

use App\DTOs\TableSort;
use App\Models\Delivery;
use App\Models\ShopOrder;
use App\Models\User;
use App\Notifications\DeliveryNotification;
use App\Notifications\DriverAssignmentNotification;
use App\Notifications\NoRiderAvailableNotification;
use App\Repositories\DeliveryRepository;
use App\Repositories\EmployeeRepository;
use App\Repositories\RiderRepository;
use App\Repositories\ShopOrderRepository;
use App\Repositories\ShopRepository;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * Dispatch of shop deliveries. Both the owner and permitted staff run it, always
 * scoped to the shop of the acting user.
 */
class LogisticsService
{
    private const STATUSES = ['pending', 'assigned', 'picked_up', 'delivered', 'failed'];

    public function __construct(
        private readonly DeliveryRepository $deliveries,
        private readonly RiderRepository $riders,
        private readonly ShopRepository $shops,
        private readonly EmployeeRepository $employees,
        private readonly ShopOrderRepository $orders,
    ) {}

    /** The shop the user dispatches for: the owner's own shop or the staff member's shop. */
    public function shopIdFor(User $user): int
    {
        $shopId = $user->isOwner()
            ? $this->shops->findByOwnerId($user->id)?->id
            : $this->employees->query()->where('user_id', $user->id)->value('shop_id');

        if ($shopId === null) {
            throw (new ModelNotFoundException)->setModel('Shop');
        }

        return (int) $shopId;
    }

    // ── Deliveries ────────────────────────────────────────────────────────────

    public function board(User $user, ?string $status, ?TableSort $sort = null): array
    {
        $shopId = $this->shopIdFor($user);

        $counts = $this->deliveries->statusCountsForShop($shopId);

        $shop = $this->shops->findShopById($shopId);

        return [
            'map' => [
                'shop' => $shop === null ? null : [
                    'public_id' => $shop->public_id,
                    'name' => $shop->shop_name,
                    'lat' => $shop->latitude,
                    'lng' => $shop->longitude,
                ],
                'stops' => $this->deliveries->routeStopsForShop($shopId)
                    ->map(fn (Delivery $delivery): array => [
                        'public_id' => $delivery->public_id,
                        'customer_name' => $delivery->customer_name,
                        'address' => $delivery->delivery_address,
                        'status' => $delivery->status,
                        'type' => $delivery->type,
                        'lat' => $delivery->destination_lat,
                        'lng' => $delivery->destination_lng,
                    ])
                    ->values()
                    ->all(),
            ],
            'deliveries' => $this->deliveries->paginateForShop($shopId, $status, 15, $sort),
            'riders' => $this->riders->activeForShop($shopId),
            'stats' => collect(self::STATUSES)
                ->mapWithKeys(fn ($key) => [$key => $counts[$key] ?? 0])
                ->all(),
        ];
    }

    private function usesOwnRider(array $data, Delivery $delivery): bool
    {
        return ($data['courier'] ?? $delivery->courier ?? Delivery::COURIER_OWN_RIDER) !== Delivery::COURIER_LALAMOVE;
    }

    /**
     * The courier columns for a delivery. A Lalamove run has no shop rider; an own-rider
     * delivery carries no courier details.
     */
    private function courierAttributes(array $data, ?Delivery $current): array
    {
        $courier = $data['courier'] ?? $current?->courier ?? Delivery::COURIER_OWN_RIDER;
        $fields = [
            'courier_reference', 'courier_tracking_url', 'courier_driver_name',
            'courier_driver_phone', 'courier_vehicle_plate', 'courier_cost',
        ];

        if ($courier !== Delivery::COURIER_LALAMOVE) {
            return ['courier' => Delivery::COURIER_OWN_RIDER] + array_fill_keys($fields, null);
        }

        $attributes = ['courier' => $courier, 'rider_id' => null];
        foreach ($fields as $field) {
            $attributes[$field] = array_key_exists($field, $data) ? $data[$field] : $current?->{$field};
        }

        return $attributes;
    }

    /**
     * A completed order the shop must bring back to the customer's address becomes a
     * delivery on the board with the customer's details already filled in. A rider is
     * attached only when one is free; otherwise it waits as pending for dispatch.
     */
    public function dispatchCompletedOrder(ShopOrder $order): ?Delivery
    {
        return $this->dispatchOrderLeg($order, Delivery::TYPE_DELIVERY, 'Created automatically from the completed order.');
    }

    /**
     * A new pickup-type order needs a rider to collect the laundry from the customer, so
     * it joins the board straight away with the customer's details and a free rider.
     */
    public function dispatchPickupRun(ShopOrder $order): ?Delivery
    {
        if (! in_array($order->status, ['pending', 'in_progress'], true)) {
            return null;
        }

        return $this->dispatchOrderLeg($order, Delivery::TYPE_PICKUP, 'Created automatically to collect the laundry from the customer.');
    }

    /**
     * One click from a finished order: a walk-in customer decided they want it delivered.
     * Everything comes from the order; only a missing address has to be supplied.
     *
     * @throws ValidationException when the order cannot be sent or has no address
     */
    public function sendOrderForDelivery(User $user, ShopOrder $order, ?string $address): Delivery
    {
        if ($order->shop_id !== $this->shopIdFor($user)) {
            throw (new ModelNotFoundException)->setModel(ShopOrder::class);
        }

        $order->loadMissing('shop');

        if (! $order->shop?->offers_delivery) {
            throw ValidationException::withMessages(['delivery' => 'This shop does not offer delivery.']);
        }

        if (! $order->shop->hasDeliveryPricing()) {
            throw ValidationException::withMessages(['delivery' => 'Set the delivery pricing in Settings before sending an order for delivery.']);
        }

        if ($order->status !== 'completed') {
            throw ValidationException::withMessages(['delivery' => 'Only completed orders can be sent for delivery.']);
        }

        if ($this->deliveries->existsForOrder($order->id)) {
            throw ValidationException::withMessages(['delivery' => 'This order already has a delivery.']);
        }

        $address = trim((string) $address);
        if ($address === '' && blank($order->customer_address) && blank($order->delivery_address)) {
            throw ValidationException::withMessages(['delivery_address' => 'Enter the address to deliver to.']);
        }

        if ($address !== '') {
            $changes = ['customer_address' => $address, 'deliver_to_customer' => true];

            // The saved pin belongs to the old address; a different address must not inherit it.
            if ($address !== trim((string) $order->customer_address)) {
                $changes += ['customer_lat' => null, 'customer_lng' => null];
            }

            $order = $this->orders->update($order, $changes);
        }

        return $this->dispatchOrderLeg($order, Delivery::TYPE_DELIVERY, 'Sent for delivery from the order.', force: true)
            ?? throw ValidationException::withMessages(['delivery' => 'This order already has a delivery.']);
    }

    /**
     * Why the laundry cannot be weighed yet, or null when it can. A pickup order is weighed at the
     * shop, so the rider must first bring it there (the pickup run marked delivered).
     */
    public function weighingBlockedReason(ShopOrder $order, ?string $pickupType = null): ?string
    {
        if (($pickupType ?? $order->pickup_type) !== 'pickup' || ! $this->shopWaitsForPickup($order->shop_id)) {
            return null;
        }

        return $this->deliveries->pickupRunReceived($order->id)
            ? null
            : 'This is a pickup order. Weigh it only after the rider has brought the laundry to the shop: mark the pickup as delivered on the Logistics board first.';
    }

    /**
     * Whether pickup orders at this shop go through a rider's pickup run. Without delivery set up
     * there is no run to wait for, so weighing is not held back.
     */
    public function shopWaitsForPickup(int $shopId): bool
    {
        return (bool) $this->shops->findShopById($shopId)?->canDeliver();
    }

    /** What the order page needs to offer the "Send for delivery" button. */
    public function deliveryOptionFor(ShopOrder $order): array
    {
        $order->loadMissing(['shop', 'delivery']);

        return [
            'offers_delivery' => (bool) $order->shop?->canDeliver(),
            'can_send' => $order->shop?->canDeliver()
                && $order->status === 'completed'
                && $order->delivery === null,
            'delivery_status' => $order->delivery?->status,
            'has_address' => filled($order->customer_address) || filled($order->delivery_address),
            'address' => $order->customer_address ?? $order->delivery_address,
        ];
    }

    private function dispatchOrderLeg(ShopOrder $order, string $type, string $note, bool $force = false): ?Delivery
    {
        $order->loadMissing(['shop', 'customer']);

        // A pickup run is only for orders the rider collects. The delivery back is made only when the
        // customer asked for it or staff sent the order for delivery; a pickup order is otherwise
        // collected at the shop, so it gets no delivery and no delivery map.
        $wanted = $type === Delivery::TYPE_PICKUP
            ? $order->pickup_type === 'pickup'
            : $force || $order->deliver_to_customer;

        if (! $wanted
            || ! $order->shop?->canDeliver()
            || $this->deliveries->existsForOrder($order->id, $type)) {
            return null;
        }

        $delivery = DB::transaction(function () use ($order, $type, $note): ?Delivery {
            // Re-check under the transaction so a double submit cannot create two runs.
            if ($this->deliveries->existsForOrder($order->id, $type)) {
                return null;
            }

            // A closed shop gives no run to a rider; it waits until the shop is open again.
            $rider = $order->shop->isOpenAt() ? $this->riders->findAvailableForShop($order->shop_id) : null;

            return $this->deliveries->createDelivery([
                'shop_id' => $order->shop_id,
                'shop_order_id' => $order->id,
                'type' => $type,
                'customer_name' => $order->customer_name,
                'customer_phone' => $order->customer_phone,
                'customer_email' => $order->customer?->email,
                'delivery_address' => $order->customer_address ?? $order->delivery_address,
                'destination_lat' => $order->customer_lat,
                'destination_lng' => $order->customer_lng,
                'rider_id' => $rider?->id,
                'status' => $rider ? 'assigned' : 'pending',
                'assigned_at' => $rider ? now() : null,
                'notes' => $rider ? $note : $note.' No rider was available.',
            ]);
        });

        if ($delivery === null) {
            return null;
        }

        $delivery->load('rider.employee.user');

        if ($delivery->rider_id) {
            $order->customer?->notify(new DeliveryNotification($delivery));
            $this->notifyDriver($delivery);
        } else {
            $this->announceNoRider($delivery);
        }

        return $delivery;
    }

    /**
     * Hands deliveries that were waiting for a rider to the riders who are free now, oldest
     * first. Called whenever a rider may have become available: a run finished, a rider was
     * added or reactivated, or a delivery left the road.
     *
     * @return int how many waiting deliveries received a rider
     */
    public function assignWaitingDeliveries(int $shopId): int
    {
        $assigned = 0;

        if (! $this->shops->findShopById($shopId)?->isOpenAt()) {
            return 0;
        }

        while (true) {
            $delivery = DB::transaction(function () use ($shopId): ?Delivery {
                foreach ($this->deliveries->waitingForShop($shopId) as $waiting) {
                    // A rider who turned this run down is not offered it again.
                    $rider = $this->riders->findAvailableForShop($shopId, $this->deliveries->rejectedRiderIds($waiting->id));

                    if ($rider) {
                        return $this->giveToRider($waiting, $rider);
                    }
                }

                return null;
            });

            if ($delivery === null) {
                break;
            }

            $assigned++;
            $delivery->load(['rider.employee.user', 'order.customer']);
            $delivery->order?->customer?->notify(new DeliveryNotification($delivery));
            $this->notifyDriver($delivery);
        }

        return $assigned;
    }

    private function giveToRider(Delivery $waiting, \App\Models\Rider $rider): Delivery
    {
        $this->deliveries->update($waiting, [
            'rider_id' => $rider->id,
            'status' => 'assigned',
            'assigned_at' => now(),
            'accepted_at' => null,
            'rider_lat' => null,
            'rider_lng' => null,
            'rider_located_at' => null,
        ]);

        return $waiting;
    }

    /** A new run lands in the driver's app and on their notification list, awaiting their answer. */
    public function notifyDriver(Delivery $delivery): void
    {
        $delivery->loadMissing(['rider.employee.user', 'order']);

        $delivery->rider?->employee?->user?->notify(new DriverAssignmentNotification($delivery));
    }

    /** Nobody can take the run right now: the customer learns it is waiting and the shop is asked to act. */
    public function announceNoRider(Delivery $delivery): void
    {
        $delivery->loadMissing(['order.customer', 'order.shop.owner']);

        $delivery->order?->customer?->notify(new NoRiderAvailableNotification($delivery, NoRiderAvailableNotification::FOR_CUSTOMER));
        $delivery->order?->shop?->owner?->notify(new NoRiderAvailableNotification($delivery, NoRiderAvailableNotification::FOR_SHOP));
    }

    /** Whether the order's run is still waiting for a rider, so the customer can be told so at once. */
    public function isWaitingForRider(ShopOrder $order): bool
    {
        return $this->deliveries->waitingForOrder($order->id);
    }

    public function updateStatus(User $user, Delivery $delivery, array $data): Delivery
    {
        $this->assertDeliveryInShop($user, $delivery);

        $timestamps = [];
        if ($data['status'] === 'assigned' && ! $delivery->assigned_at) {
            $timestamps['assigned_at'] = now();
        }
        if ($data['status'] === 'picked_up' && ! $delivery->picked_up_at) {
            $timestamps['picked_up_at'] = now();
        }
        if ($data['status'] === 'delivered' && ! $delivery->delivered_at) {
            $timestamps['delivered_at'] = now();
        }

        $courier = $this->courierAttributes($data, $delivery);

        $riderId = $data['rider_id'] ?? $delivery->rider_id;
        $riderChanged = $riderId !== $delivery->rider_id;

        if ($riderChanged && $riderId !== null && $this->usesOwnRider($data, $delivery)) {
            $rider = $this->riders->find($riderId);

            if (! $rider || ! $this->riders->isOnDuty($rider)) {
                throw ValidationException::withMessages([
                    'rider_id' => 'This rider is offline or has no shift today, so they cannot be assigned.',
                ]);
            }
        }

        DB::transaction(fn () => $this->deliveries->update($delivery, array_merge([
            'status' => $data['status'],
            'rider_id' => $riderId,
            'notes' => $data['notes'] ?? $delivery->notes,
        ], $riderChanged ? ['rider_lat' => null, 'rider_lng' => null, 'rider_located_at' => null, 'accepted_at' => null] : [], $courier, $timestamps)));

        // A rider handed a run by the shop is told straight away so they can accept or reject it.
        if ($riderChanged && $riderId !== null && $data['status'] === 'assigned') {
            $delivery->load('rider.employee.user');
            $this->notifyDriver($delivery);
        }

        // Notify the customer linked to the shop order, if any.
        $customer = $delivery->order?->customer;
        if ($customer) {
            $delivery->load('rider');
            $customer->notify(new DeliveryNotification($delivery));
        }

        $this->assignWaitingDeliveries($delivery->shop_id);

        return $delivery;
    }

    /**
     * Takes back the return delivery that completing an order created, when the order is reopened
     * after a mistake. Only a delivery nobody has collected yet can be withdrawn.
     *
     * @throws ValidationException when the rider already has the laundry
     */
    public function withdrawDeliveryForReopenedOrder(ShopOrder $order): void
    {
        $delivery = $order->delivery;

        if ($delivery === null) {
            return;
        }

        if (! in_array($delivery->status, ['pending', 'assigned'], true)) {
            throw ValidationException::withMessages([
                'status' => 'The rider already has this order for delivery, so it cannot be reopened. Handle it in Logistics.',
            ]);
        }

        $this->deliveries->delete($delivery);
    }

    public function deleteDelivery(User $user, Delivery $delivery): void
    {
        $this->assertDeliveryInShop($user, $delivery);

        $this->deliveries->delete($delivery);
    }

    // A record from another shop is reported exactly like a missing one.

    private function assertDeliveryInShop(User $user, Delivery $delivery): void
    {
        if ($delivery->shop_id !== $this->shopIdFor($user)) {
            throw (new ModelNotFoundException)->setModel(Delivery::class);
        }
    }
}
