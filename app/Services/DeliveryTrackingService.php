<?php

namespace App\Services;

use App\Exceptions\RiderLocationException;
use App\Models\Delivery;
use App\Models\ShopOrder;
use App\Models\User;
use App\Repositories\DeliveryRepository;
use Illuminate\Database\Eloquent\ModelNotFoundException;

/**
 * Live delivery tracking: the customer-facing tracking payload and rider location updates.
 */
class DeliveryTrackingService
{
    /** A rider who has not shared a position for this long is probably not sharing any more. */
    public const STALE_LOCATION_SECONDS = 180;

    public function __construct(private readonly DeliveryRepository $deliveries) {}

    public function forCustomer(User $customer, string $paymentPublicId): array
    {
        $delivery = $this->deliveries->findForCustomerOrder($paymentPublicId, $customer->id)
            ?? throw (new ModelNotFoundException)->setModel(Delivery::class);

        return $this->payload($delivery);
    }

    /** Whether the order has a rider trip to follow, so the order page can offer "Track live". */
    public function hasTrackableLeg(ShopOrder $order): bool
    {
        return $this->deliveries->orderHasLeg($order->id);
    }

    /**
     * Everything the live tracking page needs: both trips of the order and which one to follow.
     *
     *
     * @return array{order: array<string, string>, pickup: array<string, mixed>|null, delivery: array<string, mixed>|null, active: string|null}
     *
     * @throws ModelNotFoundException when the order is not the customer's or has no trip yet
     */
    public function forOrderPage(User $customer, string $paymentPublicId): array
    {
        $legs = $this->deliveries->legsForCustomerOrder($paymentPublicId, $customer->id);

        if ($legs->isEmpty()) {
            throw (new ModelNotFoundException)->setModel(Delivery::class);
        }

        $pickup = $legs->get(Delivery::TYPE_PICKUP);
        $delivery = $legs->get(Delivery::TYPE_DELIVERY);
        $order = ($delivery ?? $pickup)->order;

        return [
            'order' => [
                'order_number' => $order->order_number,
                'payment_public_id' => $order->payment_public_id,
            ],
            'pickup' => $pickup ? $this->payload($pickup) : null,
            'delivery' => $delivery ? $this->payload($delivery) : null,
            'active' => $this->activeLeg($pickup, $delivery),
        ];
    }

    /**
     * The trip to follow: the delivery back once it exists and is still going, else the pickup run
     * while it is going, else whichever finished last.
     */
    private function activeLeg(?Delivery $pickup, ?Delivery $delivery): ?string
    {
        $open = fn (?Delivery $leg) => $leg !== null && ! in_array($leg->status, ['delivered', 'failed'], true);

        return match (true) {
            $open($delivery) => Delivery::TYPE_DELIVERY,
            $open($pickup) => Delivery::TYPE_PICKUP,
            $delivery !== null => Delivery::TYPE_DELIVERY,
            $pickup !== null => Delivery::TYPE_PICKUP,
            default => null,
        };
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
            // Which trip this is: the rider collecting the laundry, or bringing it back.
            'type' => $delivery->type,
            'status' => $delivery->status,
            'is_active' => $delivery->isTrackable(),
            'delivery_address' => $delivery->delivery_address,
            'distance_km' => $delivery->distance_km !== null ? (float) $delivery->distance_km : null,
            'delivery_fee' => (float) $delivery->delivery_fee,
            'rider_name' => $delivery->rider?->name,
            // The customer of the order may call the rider; the vehicle picks the map icon.
            'rider_phone' => $delivery->isTrackable() ? $delivery->rider?->phone : null,
            'vehicle' => $delivery->rider?->vehicleKind(),
            'courier' => $delivery->courier,
            'courier_info' => $delivery->usesLalamove() ? [
                'tracking_url' => $delivery->courier_tracking_url,
                'driver_name' => $delivery->courier_driver_name,
                'driver_phone' => $delivery->isTrackable() ? $delivery->courier_driver_phone : null,
                'vehicle_plate' => $delivery->courier_vehicle_plate,
            ] : null,
            // The shop's proof photos are opened through an authenticated route by public id.
            'photos' => $delivery->photos->map(fn ($photo) => [
                'public_id' => $photo->public_id,
                'uploaded_at' => $photo->created_at?->toIso8601String(),
            ])->values()->all(),
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
                'is_stale' => $delivery->rider_located_at === null
                    || $delivery->rider_located_at->lt(now()->subSeconds(self::STALE_LOCATION_SECONDS)),
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
