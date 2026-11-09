<?php

namespace App\Repositories;

use App\DTOs\TableSort;
use App\Models\Delivery;
use App\Models\OrderRating;
use App\Models\Rider;
use App\Models\ShopOrder;
use App\Repositories\Concerns\AppliesTableSort;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

class DeliveryRepository extends Repository
{
    use AppliesTableSort;

    public function __construct(Delivery $delivery)
    {
        parent::__construct($delivery);
    }

    public function createDelivery(array $data): Delivery
    {
        return Delivery::query()->create($data);
    }

    /**
     * The longest-waiting delivery still without a rider, for the shop's own riders only.
     * Meant to be called inside the caller's transaction: the row is locked while it is claimed.
     */
    public function oldestWaitingForShop(int $shopId): ?Delivery
    {
        return Delivery::query()
            ->where('shop_id', $shopId)
            ->where('status', 'pending')
            ->whereNull('rider_id')
            ->where('courier', Delivery::COURIER_OWN_RIDER)
            ->oldest('id')
            ->lockForUpdate()
            ->first();
    }

    /**
     * Every delivery still without a rider for the shop's own riders, oldest first, so a run that
     * every free rider already turned down does not hold up the ones behind it. Meant to be called
     * inside the caller's transaction: the rows are locked while one is claimed.
     *
     * @return \Illuminate\Support\Collection<int, Delivery>
     */
    /** @return \Illuminate\Support\Collection<int, int> shops that have a delivery waiting for a rider */
    public function shopIdsWithWaitingDeliveries(): \Illuminate\Support\Collection
    {
        return Delivery::query()
            ->where('status', 'pending')
            ->whereNull('rider_id')
            ->where('courier', Delivery::COURIER_OWN_RIDER)
            ->distinct()
            ->pluck('shop_id');
    }

    public function waitingForShop(int $shopId): \Illuminate\Support\Collection
    {
        return Delivery::query()
            ->where('shop_id', $shopId)
            ->where('status', 'pending')
            ->whereNull('rider_id')
            ->where('courier', Delivery::COURIER_OWN_RIDER)
            // The order's weight decides whether the run is within the rider load limit.
            ->with('order.serviceLines')
            ->oldest('id')
            ->lockForUpdate()
            ->get();
    }

    public function waitingForOrder(int $shopOrderId): bool
    {
        return Delivery::query()
            ->where('shop_order_id', $shopOrderId)
            ->where('status', 'pending')
            ->whereNull('rider_id')
            ->exists();
    }

    /** @return list<int> riders who already turned this run down, so it is not offered to them again */
    public function rejectedRiderIds(int $deliveryId): array
    {
        return \App\Models\DeliveryRejection::query()
            ->where('delivery_id', $deliveryId)
            ->pluck('rider_id')
            ->unique()
            ->values()
            ->all();
    }

    /** Re-reads the run with its row locked; the caller owns the transaction. */
    public function lockForUpdate(Delivery $delivery): Delivery
    {
        return Delivery::query()->whereKey($delivery->id)->lockForUpdate()->firstOrFail();
    }

    public function createRejection(Delivery $delivery, int $riderId, array $data): \App\Models\DeliveryRejection
    {
        return $delivery->rejections()->create(['rider_id' => $riderId] + $data);
    }

    /**
     * Open runs that can be shown on the dispatch map: they still have somewhere to go and a pinned
     * destination. Capped so one busy shop cannot flood the map.
     *
     * @return \Illuminate\Support\Collection<int, Delivery>
     */
    public function routeStopsForShop(int $shopId, int $limit = 30): \Illuminate\Support\Collection
    {
        return Delivery::query()
            ->select(['id', 'public_id', 'customer_name', 'delivery_address', 'status', 'type', 'destination_lat', 'destination_lng'])
            ->where('shop_id', $shopId)
            ->whereIn('status', ['pending', 'assigned', 'picked_up'])
            ->whereNotNull('destination_lat')
            ->whereNotNull('destination_lng')
            ->oldest('id')
            ->limit($limit)
            ->get();
    }

    public function paginateForShop(int $shopId, ?string $status, int $perPage = 15, ?TableSort $sort = null): LengthAwarePaginator
    {
        $query = Delivery::query()
            ->with([
                'rider',
                // The weight columns let the board flag a run that is outside the rider load limit.
                'order:id,order_number,pricing_model,actual_weight_kg,estimated_weight_kg',
                'order.serviceLines:id,shop_order_id,pricing_model,weight_kg',
                'photos',
            ])
            ->where('shop_id', $shopId)
            ->when($status, fn ($query) => $query->where('status', $status));

        $sorted = $this->applyTableSort($query, $sort, [
            'customer_name' => 'customer_name',
            'status' => 'status',
            'created_at' => 'created_at',
            'order' => fn ($q, $dir) => $q->orderBy(
                ShopOrder::query()
                    ->select('order_number')
                    ->whereColumn('shop_orders.id', 'deliveries.shop_order_id'),
                $dir,
            ),
            'rider' => fn ($q, $dir) => $q->orderBy(
                Rider::query()
                    ->select('name')
                    ->whereColumn('riders.id', 'deliveries.rider_id'),
                $dir,
            ),
        ]);

        if (! $sorted) {
            $query->latest();
        }

        return $query->paginate($perPage)->withQueryString();
    }

    /** @return array<string, int> delivery counts keyed by status, for the shop */
    public function statusCountsForShop(int $shopId): array
    {
        return Delivery::query()
            ->where('shop_id', $shopId)
            ->selectRaw('status, COUNT(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status')
            ->map(fn ($total) => (int) $total)
            ->all();
    }

    public function existsForOrder(int $shopOrderId, string $type = Delivery::TYPE_DELIVERY): bool
    {
        return Delivery::query()
            ->where('shop_order_id', $shopOrderId)
            ->where('type', $type)
            ->exists();
    }

    /** Whether the rider has brought the customer's laundry to the shop (the pickup run is delivered). */
    public function pickupRunReceived(int $shopOrderId): bool
    {
        return Delivery::query()
            ->where('shop_order_id', $shopOrderId)
            ->where('type', Delivery::TYPE_PICKUP)
            ->where('status', 'delivered')
            ->exists();
    }

    /**
     * The delivery of an order owned by the given customer. Anything else (another
     * customer's order, unknown id, or no delivery yet) resolves to null.
     */
    public function findForCustomerOrder(string $paymentPublicId, int $customerId): ?Delivery
    {
        return Delivery::query()
            ->with(['shop', 'rider:id,name,phone,vehicle_type', 'photos', 'order:id,order_number,payment_public_id,user_id'])
            // Only the delivery back to the customer is tracked here, never the pickup run.
            ->where('type', Delivery::TYPE_DELIVERY)
            ->whereHas('order', fn ($query) => $query
                ->where('payment_public_id', $paymentPublicId)
                ->where('user_id', $customerId))
            ->first();
    }

    /** Whether the order has any rider trip, pickup or delivery. */
    public function orderHasLeg(int $shopOrderId): bool
    {
        return Delivery::query()->where('shop_order_id', $shopOrderId)->exists();
    }

    /**
     * Both trips of an order owned by the given customer, keyed by type (pickup, delivery). Another
     * customer's order, an unknown id, or an order with no trip yet resolves to an empty collection.
     *
     * @return Collection<string, Delivery>
     */
    public function legsForCustomerOrder(string $paymentPublicId, int $customerId): Collection
    {
        return Delivery::query()
            ->with(['shop', 'rider:id,name,phone,vehicle_type', 'photos', 'order:id,order_number,payment_public_id,user_id'])
            ->whereHas('order', fn ($query) => $query
                ->where('payment_public_id', $paymentPublicId)
                ->where('user_id', $customerId))
            // The newest trip of each type wins if an order ever has more than one.
            ->orderBy('id')
            ->get()
            ->keyBy('type');
    }

    public function updateRiderLocation(Delivery $delivery, float $lat, float $lng): Delivery
    {
        $delivery->update([
            'rider_lat' => $lat,
            'rider_lng' => $lng,
            'rider_located_at' => now(),
        ]);

        return $delivery;
    }

    // ── Driver app: everything is scoped to one rider ─────────────────────────

    private const WITH_FOR_DRIVER = ['order:id,shop_id,order_number,service_id,payment_public_id,total_amount,amount_paid,alternate_contact_name,alternate_contact_phone', 'order.service:id,service_name', 'shop:id,shop_name,latitude,longitude,block_street,barangay,municipality'];

    /** @return Collection<int, Delivery> the rider's runs still on the road, oldest first */
    public function openForRider(int $riderId): Collection
    {
        return Delivery::query()
            ->with(self::WITH_FOR_DRIVER)
            ->where('rider_id', $riderId)
            ->whereIn('status', ['assigned', 'picked_up'])
            ->orderBy('assigned_at')
            ->orderBy('id')
            ->get();
    }

    /** @return Collection<int, Delivery> runs the rider finished today, newest first */
    public function finishedTodayForRider(int $riderId): Collection
    {
        return Delivery::query()
            ->with(self::WITH_FOR_DRIVER)
            ->where('rider_id', $riderId)
            ->whereIn('status', ['delivered', 'failed'])
            ->whereDate('updated_at', today())
            ->latest('updated_at')
            ->get();
    }

    public function findForRider(string $publicId, int $riderId): ?Delivery
    {
        return Delivery::query()
            ->with(self::WITH_FOR_DRIVER)
            ->where('public_id', $publicId)
            ->where('rider_id', $riderId)
            ->first();
    }

    /** @return Collection<int, Delivery> finished runs of the rider within the period, newest first */
    public function finishedBetweenForRider(int $riderId, Carbon $from, Carbon $to): Collection
    {
        return Delivery::query()
            ->with(['order:id,order_number,delivery_fee'])
            ->where('rider_id', $riderId)
            ->where('status', 'delivered')
            ->whereBetween('delivered_at', [$from, $to])
            ->latest('delivered_at')
            ->get();
    }

    /** Average star rating the customers gave the orders this rider delivered. */
    public function averageRatingForRider(int $riderId): ?float
    {
        $average = OrderRating::query()
            ->whereIn('shop_order_id', Delivery::query()
                ->where('rider_id', $riderId)
                ->where('status', 'delivered')
                ->select('shop_order_id'))
            ->avg('rating');

        return $average === null ? null : round((float) $average, 1);
    }
}
