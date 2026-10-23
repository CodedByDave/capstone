<?php

namespace App\Repositories;

use App\Models\Delivery;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class DeliveryRepository extends Repository
{
    public function __construct(Delivery $delivery)
    {
        parent::__construct($delivery);
    }

    public function createDelivery(array $data): Delivery
    {
        return Delivery::query()->create($data);
    }

    public function paginateForShop(int $shopId, ?string $status, int $perPage = 15): LengthAwarePaginator
    {
        return Delivery::query()
            ->with(['rider', 'order:id,order_number', 'photos'])
            ->where('shop_id', $shopId)
            ->when($status, fn ($query) => $query->where('status', $status))
            ->latest()
            ->paginate($perPage)
            ->withQueryString();
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
            ->with(['shop', 'rider:id,name', 'photos', 'order:id,order_number,payment_public_id,user_id'])
            // Only the delivery back to the customer is tracked here, never the pickup run.
            ->where('type', Delivery::TYPE_DELIVERY)
            ->whereHas('order', fn ($query) => $query
                ->where('payment_public_id', $paymentPublicId)
                ->where('user_id', $customerId))
            ->first();
    }

    public function findByDriverToken(string $token): ?Delivery
    {
        return Delivery::query()->where('driver_token', $token)->first();
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
}
