<?php

namespace App\Services;

use App\Exceptions\RiderLocationException;
use App\Models\Delivery;
use App\Models\User;
use App\Repositories\DeliveryRepository;
use Illuminate\Database\Eloquent\ModelNotFoundException;

/**
 * Live delivery tracking: the customer-facing tracking payload and rider location updates.
 */
class DeliveryTrackingService
{
    public function __construct(private readonly DeliveryRepository $deliveries) {}

    public function forCustomer(User $customer, string $paymentPublicId): array
    {
        $delivery = $this->deliveries->findForCustomerOrder($paymentPublicId, $customer->id)
            ?? throw (new ModelNotFoundException)->setModel(Delivery::class);

        return $this->payload($delivery);
    }

    /**
     * Customer-facing delivery data. Contains no sequential IDs or rider tokens.
     */
    public function payload(Delivery $delivery): array
    {
        $shop = $delivery->shop;
        $showRider = $delivery->isTrackable()
            && $delivery->rider_lat !== null
            && $delivery->rider_lng !== null;

        return [
            'public_id' => $delivery->public_id,
            'status' => $delivery->status,
            'is_active' => $delivery->isTrackable(),
            'delivery_address' => $delivery->delivery_address,
            'distance_km' => $delivery->distance_km !== null ? (float) $delivery->distance_km : null,
            'delivery_fee' => (float) $delivery->delivery_fee,
            'rider_name' => $delivery->rider?->name,
            'shop' => [
                'name' => $shop?->shop_name,
                'lat' => $shop?->latitude,
                'lng' => $shop?->longitude,
            ],
            'destination' => [
                'lat' => $delivery->destination_lat,
                'lng' => $delivery->destination_lng,
            ],
            'rider' => $showRider ? [
                'lat' => $delivery->rider_lat,
                'lng' => $delivery->rider_lng,
                'located_at' => $delivery->rider_located_at?->toIso8601String(),
            ] : null,
            'timeline' => [
                'requested_at' => $delivery->created_at?->toIso8601String(),
                'assigned_at' => $delivery->assigned_at?->toIso8601String(),
                'picked_up_at' => $delivery->picked_up_at?->toIso8601String(),
                'delivered_at' => $delivery->delivered_at?->toIso8601String(),
                'failed_at' => $delivery->status === 'failed'
                    ? $delivery->updated_at?->toIso8601String()
                    : null,
            ],
        ];
    }

    /**
     * @throws ModelNotFoundException for an unknown token
     * @throws RiderLocationException when the delivery is not on the road
     */
    public function updateRiderLocation(string $driverToken, float $lat, float $lng): Delivery
    {
        $delivery = $this->deliveries->findByDriverToken($driverToken)
            ?? throw (new ModelNotFoundException)->setModel(Delivery::class);

        if (! $delivery->isTrackable()) {
            throw new RiderLocationException('Live location can be shared only while the delivery is assigned or picked up.');
        }

        return $this->deliveries->updateRiderLocation($delivery, $lat, $lng);
    }
}
