<?php

namespace App\Repositories;

use App\Models\Delivery;

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

    public function existsForOrder(int $shopOrderId): bool
    {
        return Delivery::query()->where('shop_order_id', $shopOrderId)->exists();
    }

    /**
     * The delivery of an order owned by the given customer. Anything else (another
     * customer's order, unknown id, or no delivery yet) resolves to null.
     */
    public function findForCustomerOrder(string $paymentPublicId, int $customerId): ?Delivery
    {
        return Delivery::query()
            ->with(['shop', 'rider:id,name', 'order:id,order_number,payment_public_id,user_id'])
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
