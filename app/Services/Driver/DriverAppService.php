<?php

namespace App\Services\Driver;

use App\Exceptions\DriverJobException;
use App\Exceptions\RiderLocationException;
use App\Models\Delivery;
use App\Models\Rider;
use App\Notifications\DeliveryNotification;
use App\Repositories\DeliveryRepository;
use App\Repositories\RiderRepository;
use App\Services\LogisticsService;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * The driver app: a driver sees and moves only the runs assigned to them. A run keeps the
 * same flow as on the shop's board: assigned, picked up, then delivered (or failed).
 */
class DriverAppService
{
    /** The order a run moves through; it may never go back. */
    private const FLOW = ['assigned', 'picked_up', 'delivered', 'failed'];

    public function __construct(
        private readonly DeliveryRepository $deliveries,
        private readonly RiderRepository $riders,
        private readonly LogisticsService $logistics,
        private readonly DriverEarningsService $earnings,
    ) {}

    // ── Pages ─────────────────────────────────────────────────────────────────

    public function home(Rider $rider): array
    {
        $open = $this->deliveries->openForRider($rider->id);
        $done = $this->deliveries->finishedTodayForRider($rider->id);
        $delivered = $done->where('status', 'delivered');

        return [
            'driver' => $this->driverPayload($rider),
            'today' => [
                'earnings' => $this->earnings->today($rider),
                'jobs_completed' => $delivered->count(),
                'jobs_total' => $open->count() + $done->count(),
                'distance_km' => round((float) $delivered->sum(fn (Delivery $run) => (float) $run->distance_km), 1),
                'orders' => $delivered->pluck('shop_order_id')->unique()->count(),
                'rating' => $this->deliveries->averageRatingForRider($rider->id),
            ],
            'next_stop' => $open->isEmpty() ? null : $this->jobPayload($open->first()),
            'queue' => [
                'pickups' => $this->payloads($open->where('type', Delivery::TYPE_PICKUP)),
                'deliveries' => $this->payloads($open->where('type', Delivery::TYPE_DELIVERY)),
                'done' => $this->payloads($done),
            ],
        ];
    }

    public function jobs(Rider $rider): array
    {
        return [
            'driver' => $this->driverPayload($rider),
            'open' => $this->payloads($this->deliveries->openForRider($rider->id)),
            'done' => $this->payloads($this->deliveries->finishedTodayForRider($rider->id)),
        ];
    }

    public function job(Rider $rider, string $publicId): array
    {
        return [
            'driver' => $this->driverPayload($rider),
            'job' => $this->jobPayload($this->findOwnJob($rider, $publicId)),
        ];
    }

    public function map(Rider $rider): array
    {
        $open = $this->deliveries->openForRider($rider->id);

        return [
            'driver' => $this->driverPayload($rider),
            'job' => $open->isEmpty() ? null : $this->jobPayload($open->first()),
        ];
    }

    public function earningsPage(Rider $rider): array
    {
        return [
            'driver' => $this->driverPayload($rider),
            'earnings' => $this->earnings->history($rider),
            'today' => $this->earnings->today($rider),
        ];
    }

    public function profile(Rider $rider): array
    {
        return [
            'driver' => $this->driverPayload($rider),
            'rating' => $this->deliveries->averageRatingForRider($rider->id),
        ];
    }

    // ── Actions ───────────────────────────────────────────────────────────────

    public function setOnline(Rider $rider, bool $online): Rider
    {
        $rider = DB::transaction(fn () => $this->riders->setOnline($rider, $online));

        // A driver who just came online may take an order that was waiting for a driver.
        if ($online) {
            $this->logistics->assignWaitingDeliveries($rider->shop_id);
        }

        return $rider;
    }

    /**
     * @throws ModelNotFoundException when the run is not this driver's
     * @throws DriverJobException when the run cannot move to that status
     */
    public function updateStatus(Rider $rider, string $publicId, string $status, ?string $notes): Delivery
    {
        $delivery = $this->findOwnJob($rider, $publicId);

        $current = array_search($delivery->status, self::FLOW, true);
        $next = array_search($status, self::FLOW, true);

        if ($current === false || $next === false || $next <= $current) {
            throw new DriverJobException("Cannot change from '{$delivery->status}' to '{$status}'.");
        }

        $changes = ['status' => $status, 'notes' => $notes ?? $delivery->notes];
        if ($status === 'picked_up' && ! $delivery->picked_up_at) {
            $changes['picked_up_at'] = now();
        }
        if ($status === 'delivered' && ! $delivery->delivered_at) {
            $changes['delivered_at'] = now();
        }

        DB::transaction(function () use ($delivery, $rider, $changes, $status) {
            $this->deliveries->update($delivery, $changes);

            // A finished run puts the driver back in line for the next order.
            if (in_array($status, ['delivered', 'failed'], true)) {
                $this->riders->markFree($rider);
            }
        });

        $delivery->load(['rider', 'order.customer']);
        $delivery->order?->customer?->notify(new DeliveryNotification($delivery));

        $this->logistics->assignWaitingDeliveries($delivery->shop_id);

        return $delivery;
    }

    /**
     * @throws ModelNotFoundException when the run is not this driver's
     * @throws RiderLocationException when the run is not on the road
     */
    public function updateLocation(Rider $rider, string $publicId, float $lat, float $lng): Delivery
    {
        $delivery = $this->findOwnJob($rider, $publicId);

        if (! $delivery->isTrackable()) {
            throw new RiderLocationException('Live location can be shared only while the delivery is assigned or picked up.');
        }

        return $this->deliveries->updateRiderLocation($delivery, $lat, $lng);
    }

    // ── Presentation ──────────────────────────────────────────────────────────

    private function findOwnJob(Rider $rider, string $publicId): Delivery
    {
        return $this->deliveries->findForRider($publicId, $rider->id)
            ?? throw (new ModelNotFoundException)->setModel(Delivery::class);
    }

    private function driverPayload(Rider $rider): array
    {
        return [
            'name' => $rider->name,
            'phone' => $rider->phone,
            'vehicle_type' => $rider->vehicle_type,
            'is_online' => (bool) $rider->is_online,
            'shop_name' => $rider->shop?->shop_name,
        ];
    }

    /** @param Collection<int, Delivery> $runs */
    private function payloads(Collection $runs): array
    {
        return $runs->map(fn (Delivery $run) => $this->jobPayload($run))->values()->all();
    }

    /**
     * One run for the app. The stop to head to depends on the run: a pickup run goes to the
     * customer first and then to the shop; a delivery run goes to the shop first and then to
     * the customer.
     */
    private function jobPayload(Delivery $run): array
    {
        $shop = $run->shop;
        $number = $run->order?->order_number;

        $headingToCustomer = $run->status === 'picked_up' ? ! $run->isPickupRun() : $run->isPickupRun();

        $customerStop = [
            'label' => $run->customer_name,
            'address' => $run->delivery_address,
            'lat' => $run->destination_lat,
            'lng' => $run->destination_lng,
        ];
        $shopStop = [
            'label' => $shop?->shop_name,
            'address' => collect([$shop?->block_street, $shop?->barangay, $shop?->municipality])->filter()->implode(', '),
            'lat' => $shop?->latitude !== null ? (float) $shop->latitude : null,
            'lng' => $shop?->longitude !== null ? (float) $shop->longitude : null,
        ];

        $finished = in_array($run->status, ['delivered', 'failed'], true);

        return [
            'public_id' => $run->public_id,
            'type' => $run->type,
            'status' => $run->status,
            'order_number' => $number,
            'service' => $run->order?->service?->service_name,
            'customer_name' => $run->customer_name,
            'customer_phone' => $run->customer_phone,
            'address' => $run->delivery_address,
            'notes' => $run->notes,
            'distance_km' => $run->distance_km !== null ? (float) $run->distance_km : null,
            'assigned_at' => $run->assigned_at?->toIso8601String(),
            'finished_at' => $finished ? $run->updated_at?->toIso8601String() : null,
            'target' => ($headingToCustomer ? $customerStop : $shopStop) + ['kind' => $headingToCustomer ? 'customer' : 'shop'],
            'next_status' => match ($run->status) {
                'assigned' => 'picked_up',
                'picked_up' => 'delivered',
                default => null,
            },
            'action_label' => $this->actionLabel($run),
            'checklist' => $this->checklist($run, $number),
        ];
    }

    private function actionLabel(Delivery $run): ?string
    {
        return match (true) {
            $run->status === 'assigned' && $run->isPickupRun() => 'Confirm pickup',
            $run->status === 'assigned' => 'Confirm pickup from shop',
            $run->status === 'picked_up' && $run->isPickupRun() => 'Confirm arrival at shop',
            $run->status === 'picked_up' => 'Confirm delivery',
            default => null,
        };
    }

    /** @return list<string> things the driver ticks off before confirming the step */
    private function checklist(Delivery $run, ?string $number): array
    {
        $order = $number ? "#{$number}" : 'the order';

        return match (true) {
            $run->status === 'assigned' && $run->isPickupRun() => ['Collect the laundry bags from the customer', "Tag the bags with order {$order}"],
            $run->status === 'assigned' => ["Pick up the packed bags for {$order}", 'Check the delivery address with the order'],
            $run->status === 'picked_up' && $run->isPickupRun() => ['Hand the laundry over to the shop staff'],
            $run->status === 'picked_up' => ['Hand the laundry over to the customer'],
            default => [],
        };
    }
}
