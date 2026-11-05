<?php

namespace App\Services\Driver;

use App\Exceptions\DriverJobException;
use App\Exceptions\RiderLocationException;
use App\Models\Delivery;
use App\Models\DeliveryPhoto;
use App\Models\Rider;
use App\Models\ShopOrderPayment;
use App\Models\User;
use App\Notifications\DeliveryNotification;
use App\Notifications\DeliveryRejectedNotification;
use App\Repositories\DeliveryRepository;
use App\Repositories\NotificationRepository;
use App\Repositories\RiderRepository;
use App\Services\DeliveryProofService;
use App\Services\LogisticsService;
use App\Services\OrderPaymentService;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

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
        private readonly DriverAttendanceService $attendance,
        private readonly OrderPaymentService $orderPayments,
        private readonly DeliveryProofService $proofs,
        private readonly NotificationRepository $notifications,
    ) {}

    // ── Pages ─────────────────────────────────────────────────────────────────

    public function home(Rider $rider): array
    {
        $open = $this->deliveries->openForRider($rider->id);
        $done = $this->deliveries->finishedTodayForRider($rider->id);
        $delivered = $done->where('status', 'delivered');

        return [
            'driver' => $this->driverPayload($rider),
            'attendance' => $this->attendance->state($rider),
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
            'attendance' => $this->attendance->state($rider),
            'attendance_history' => $this->attendance->history($rider),
        ];
    }

    // ── Actions ───────────────────────────────────────────────────────────────

    /** @throws DriverJobException when the driver tries to go online without a shift today */
    public function setOnline(Rider $rider, bool $online): Rider
    {
        if ($online && $this->attendance->state($rider)['blocked_reason'] !== null) {
            throw new DriverJobException('You can only go online on a day in your work schedule.');
        }

        $rider = DB::transaction(fn () => $this->riders->setOnline($rider, $online));

        // A driver who just came online may take an order that was waiting for a driver.
        if ($online) {
            $this->logistics->assignWaitingDeliveries($rider->shop_id);
        }

        return $rider;
    }

    /**
     * The driver says yes to a run they were just assigned; only then does it count as theirs to start.
     *
     * @throws ModelNotFoundException when the run is not this driver's
     * @throws DriverJobException when the run is not waiting for an answer
     */
    public function accept(Rider $rider, string $publicId): Delivery
    {
        $delivery = $this->findOwnJob($rider, $publicId);

        if (! $delivery->awaitsAcceptance()) {
            throw new DriverJobException('This order is no longer waiting for your answer.');
        }

        $this->deliveries->update($delivery, ['accepted_at' => now()]);

        return $delivery;
    }

    /**
     * The driver turns a run down with a reason and a photo as proof (an emergency, a breakdown...).
     * The run goes back in line for the next free driver, never this one, and the driver goes offline
     * so nothing else is handed to them while they sort their problem out.
     *
     * @throws ModelNotFoundException when the run is not this driver's
     * @throws DriverJobException when the run is already on the road
     */
    public function reject(Rider $rider, string $publicId, string $reason, ?string $note, UploadedFile $photo): Delivery
    {
        $delivery = $this->findOwnJob($rider, $publicId);

        if ($delivery->status !== 'assigned') {
            throw new DriverJobException('You can only reject an order before you pick it up.');
        }

        $path = $photo->store("delivery-rejections/{$delivery->shop_id}/{$delivery->public_id}", 'private');

        try {
            $rejection = DB::transaction(function () use ($delivery, $rider, $reason, $note, $path) {
                $rejection = $this->deliveries->createRejection($delivery, $rider->id, [
                    'reason' => $reason,
                    'note' => $note,
                    'photo_path' => $path,
                ]);

                $this->deliveries->update($delivery, [
                    'rider_id' => null,
                    'status' => 'pending',
                    'assigned_at' => null,
                    'accepted_at' => null,
                    'rider_lat' => null,
                    'rider_lng' => null,
                    'rider_located_at' => null,
                ]);

                $this->riders->setOnline($rider, false);

                return $rejection;
            });
        } catch (\Throwable $exception) {
            Storage::disk('private')->delete($path);

            throw $exception;
        }

        $delivery->load(['order.customer', 'order.shop.owner']);
        $delivery->order?->shop?->owner?->notify(new DeliveryRejectedNotification($delivery, $rejection, $rider->name));

        // The next free driver gets it; if nobody can, the customer and shop are told.
        $this->logistics->assignWaitingDeliveries($delivery->shop_id);

        if ($this->deliveries->waitingForOrder((int) $delivery->shop_order_id)) {
            $this->logistics->announceNoRider($delivery);
        }

        return $delivery->refresh();
    }

    /** @return list<array{id: string, title: string, body: string, url: ?string}> new-run alerts the app has not shown yet */
    public function alerts(User $user): array
    {
        return $this->notifications->unreadOfTypes($user, ['driver_assignment'], 10)
            ->map(fn ($notification) => [
                'id' => $notification->id,
                'title' => $notification->data['title'] ?? '',
                'body' => $notification->data['body'] ?? '',
                'url' => $notification->data['url'] ?? null,
            ])
            ->values()
            ->all();
    }

    public function markAlertRead(User $user, string $id): void
    {
        $this->notifications->markRead($user, $id);
    }

    /**
     * @throws ModelNotFoundException when the run is not this driver's
     * @throws DriverJobException when the run cannot move to that status, or the cash collected is not valid
     */
    public function updateStatus(Rider $rider, string $publicId, string $status, ?string $notes, ?float $collected = null, ?User $actor = null, ?UploadedFile $cashProof = null): Delivery
    {
        $delivery = $this->findOwnJob($rider, $publicId);

        $current = array_search($delivery->status, self::FLOW, true);
        $next = array_search($status, self::FLOW, true);

        if ($current === false || $next === false || $next <= $current) {
            throw new DriverJobException("Cannot change from '{$delivery->status}' to '{$status}'.");
        }

        // Handing the laundry over is when the balance is paid, so the rider says what they collected.
        $toCollect = $status === 'delivered' ? $this->amountToCollect($delivery) : null;

        if ($toCollect !== null && $collected === null) {
            throw new DriverJobException('Enter the cash you collected from the customer (0 if nothing).');
        }

        if ($toCollect !== null && round($collected, 2) > $toCollect) {
            throw new DriverJobException('You cannot collect more than the balance of ₱'.number_format($toCollect, 2).'.');
        }

        // Cash that changes hands needs proof the shop can look at later.
        if ($toCollect !== null && $collected > 0 && $cashProof === null) {
            throw new DriverJobException('Take a photo of the cash you collected as proof.');
        }

        // Collecting the laundry from the customer needs a photo of it as proof.
        $needsPickupProof = $status === 'picked_up' && $delivery->isPickupRun();

        if ($needsPickupProof && $cashProof === null) {
            throw new DriverJobException('Take a photo of the laundry you picked up as proof.');
        }

        $changes = ['status' => $status, 'notes' => $notes ?? $delivery->notes];
        // Going ahead with a run counts as accepting it.
        if (! $delivery->accepted_at) {
            $changes['accepted_at'] = now();
        }
        if ($status === 'picked_up' && ! $delivery->picked_up_at) {
            $changes['picked_up_at'] = now();
        }
        if ($status === 'delivered' && ! $delivery->delivered_at) {
            $changes['delivered_at'] = now();
        }

        DB::transaction(function () use ($delivery, $rider, $changes, $status, $toCollect, $collected, $actor, $cashProof, $needsPickupProof) {
            $this->deliveries->update($delivery, $changes);

            if ($needsPickupProof && $actor !== null) {
                $this->proofs->add($actor, $delivery, [$cashProof], DeliveryPhoto::KIND_PICKUP);
            }

            // Cash collected at the door is recorded as a payment on the order, by the rider.
            if ($toCollect !== null && $collected > 0 && $actor !== null && $delivery->order !== null) {
                $this->orderPayments->recordShopPayment($actor, $delivery->order, [
                    'amount' => round($collected, 2),
                    'method' => 'cash',
                    'note' => "Collected on delivery by {$rider->name}.",
                ]);

                $this->proofs->add($actor, $delivery, [$cashProof], DeliveryPhoto::KIND_CASH);
            }

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

    /**
     * A driver with no shift today cannot be online, so a stale online flag (set on an earlier
     * working day) is switched off whenever the app loads.
     */
    private function offlineWhenNotScheduled(Rider $rider): void
    {
        if ($rider->is_online && $this->attendance->state($rider)['blocked_reason'] !== null) {
            $this->riders->setOnline($rider, false);
        }
    }

    private function driverPayload(Rider $rider): array
    {
        $this->offlineWhenNotScheduled($rider);

        return [
            'name' => $rider->name,
            'email' => $rider->employee?->email,
            'avatar' => $rider->employee?->user?->profilePhotoUrl(),
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
    /**
     * What the rider should collect when handing the laundry over: the balance still owed on the order,
     * delivery fee included. Null when this run collects nothing (a pickup run, no order, or already paid).
     */
    private function amountToCollect(Delivery $run): ?float
    {
        if ($run->isPickupRun() || $run->order === null) {
            return null;
        }

        $balance = $run->order->balanceDue();

        return $balance > 0 ? $balance : null;
    }

    /** @return list<array{method: string, amount: float, reference: string|null, source: string, paid_at: string|null}> */
    private function confirmedPayments(Delivery $run): array
    {
        if ($run->order === null) {
            return [];
        }

        return $this->orderPayments->history($run->order)
            ->filter(fn (ShopOrderPayment $payment) => $payment->isConfirmed())
            ->map(fn (ShopOrderPayment $payment) => [
                'method' => $payment->method,
                'amount' => (float) $payment->amount,
                'reference' => $payment->reference,
                'source' => $payment->source,
                'paid_at' => ($payment->paid_at ?? $payment->created_at)?->toIso8601String(),
            ])
            ->values()
            ->all();
    }

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
            'alternate_contact_name' => $run->order?->alternate_contact_name,
            'alternate_contact_phone' => $run->order?->alternate_contact_phone,
            'address' => $run->delivery_address,
            'notes' => $run->notes,
            // Shown until the run is finished; what the rider collects in cash at the door.
            'collect_amount' => $finished ? null : $this->amountToCollect($run),
            // What the customer already paid (online or at the shop), so the rider can see it is settled.
            'paid_payments' => $finished || $run->isPickupRun() ? [] : $this->confirmedPayments($run),
            'distance_km' => $run->distance_km !== null ? (float) $run->distance_km : null,
            'assigned_at' => $run->assigned_at?->toIso8601String(),
            'finished_at' => $finished ? $run->updated_at?->toIso8601String() : null,
            // A freshly assigned run waits for the driver to accept or reject it before they set off.
            'awaiting_acceptance' => $run->awaitsAcceptance(),
            'target' => ($headingToCustomer ? $customerStop : $shopStop) + ['kind' => $headingToCustomer ? 'customer' : 'shop'],
            'customer_stop' => $customerStop + ['kind' => 'customer'],
            'shop_stop' => $shopStop + ['kind' => 'shop'],
            'next_status' => match ($run->status) {
                'assigned' => 'picked_up',
                'picked_up' => 'delivered',
                default => null,
            },
            // True when the rider must photograph the laundry before confirming the pickup.
            'requires_pickup_photo' => $run->status === 'assigned' && $run->isPickupRun(),
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
