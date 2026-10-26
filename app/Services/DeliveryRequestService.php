<?php

namespace App\Services;

use App\Exceptions\DeliveryNotAvailableException;
use App\Exceptions\DeliveryRequestException;
use App\Models\Delivery;
use App\Models\Shop;
use App\Models\ShopOrder;
use App\Models\User;
use App\Notifications\DeliveryNotification;
use App\Repositories\DeliveryRepository;
use App\Repositories\ShopOrderRepository;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\DB;

/**
 * Customer-initiated delivery of a completed, paid order: quoting the fee for a
 * destination and creating the delivery with that fee added to the order total.
 */
class DeliveryRequestService
{
    public function __construct(
        private readonly ShopOrderRepository $orders,
        private readonly DeliveryRepository $deliveries,
        private readonly DistanceService $distance,
        private readonly DeliveryFeeService $fees,
    ) {}

    /**
     * @return array{distance_km: float|null, fee: float|null, breakdown: array<string, mixed>|null, available: bool, message: string}
     */
    public function quote(User $customer, string $paymentPublicId, float $lat, float $lng): array
    {
        $order = $this->ownedOrder($customer, $paymentPublicId);
        $distanceKm = null;

        try {
            $this->fees->assertShopCanQuote($order->shop);
            $distanceKm = $this->distanceTo($order->shop, $lat, $lng);
            $breakdown = $this->fees->breakdown($order->shop, $distanceKm);
            $fee = $breakdown['fee'];
        } catch (DeliveryNotAvailableException $exception) {
            return [
                'distance_km' => $distanceKm,
                'fee' => null,
                'breakdown' => null,
                'available' => false,
                'message' => $exception->getMessage(),
            ];
        }

        return [
            'distance_km' => $distanceKm,
            'fee' => $fee,
            'breakdown' => $breakdown,
            'available' => true,
            'message' => $fee > 0
                ? 'Delivery fee of ₱'.number_format($fee, 2).' will be added to your balance.'
                : 'Delivery to this address is free.',
        ];
    }

    /**
     * @param  array{delivery_address: string, destination_lat: float|string, destination_lng: float|string}  $data
     *
     * @throws ModelNotFoundException when the order is not the customer's
     * @throws DeliveryRequestException when the order is not eligible
     * @throws DeliveryNotAvailableException when the shop cannot deliver to the destination
     */
    public function request(User $customer, string $paymentPublicId, array $data): Delivery
    {
        $order = $this->ownedOrder($customer, $paymentPublicId);
        $this->assertEligible($order);

        $lat = (float) $data['destination_lat'];
        $lng = (float) $data['destination_lng'];

        // The routing call happens before the transaction so no row lock is held during network I/O.
        $this->fees->assertShopCanQuote($order->shop);
        $distanceKm = $this->distanceTo($order->shop, $lat, $lng);
        $fee = $this->fees->calculate($order->shop, $distanceKm);

        $delivery = DB::transaction(function () use ($order, $data, $lat, $lng, $distanceKm, $fee): Delivery {
            $locked = $this->orders->lockForUpdate($order);
            $this->assertEligible($locked);

            $delivery = $this->deliveries->createDelivery([
                'shop_id' => $locked->shop_id,
                'shop_order_id' => $locked->id,
                'customer_name' => $locked->customer_name,
                'customer_phone' => $locked->customer_phone,
                'delivery_address' => $data['delivery_address'],
                'destination_lat' => $lat,
                'destination_lng' => $lng,
                'distance_km' => $distanceKm,
                'delivery_fee' => $fee,
                'status' => 'pending',
                'notes' => 'Requested by customer via app.',
            ]);

            $this->orders->update($locked, $this->totalsWithDeliveryFee($locked, $fee) + [
                'delivery_address' => $data['delivery_address'],
            ]);

            return $delivery;
        });

        $order->shop->owner?->notify(new DeliveryNotification($delivery));

        return $delivery;
    }

    /**
     * Adds the delivery fee to the total and re-derives the payment status, so an order
     * that was fully paid becomes partially paid with the fee as the balance due.
     */
    private function totalsWithDeliveryFee(ShopOrder $order, float $fee): array
    {
        $total = round((float) $order->total_amount - (float) $order->delivery_fee + $fee, 2);
        $amountPaid = (float) $order->amount_paid;

        $status = match (true) {
            $amountPaid <= 0 => 'unpaid',
            $amountPaid < $total => 'partial',
            default => 'paid',
        };

        return [
            'delivery_fee' => $fee,
            'total_amount' => $total,
            'payment_status' => $status,
            'paid_at' => $status === 'paid' ? ($order->paid_at ?? now()) : null,
        ];
    }

    private function assertEligible(ShopOrder $order): void
    {
        if ($order->status !== 'completed' || $order->payment_status !== 'paid') {
            throw new DeliveryRequestException('Delivery can be requested only after the order is completed and fully paid.');
        }

        if ($this->deliveries->existsForOrder($order->id)) {
            throw new DeliveryRequestException('A delivery has already been requested for this order.');
        }
    }

    private function distanceTo(Shop $shop, float $lat, float $lng): float
    {
        return $this->distance->between((float) $shop->latitude, (float) $shop->longitude, $lat, $lng)['distance_km'];
    }

    private function ownedOrder(User $customer, string $paymentPublicId): ShopOrder
    {
        return $this->orders->findForCustomerDelivery($paymentPublicId, $customer->id)
            ?? throw (new ModelNotFoundException)->setModel(ShopOrder::class);
    }
}
