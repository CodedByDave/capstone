<?php

namespace App\Services;

use App\Exceptions\OrderComplaintException;
use App\Models\OrderComplaint;
use App\Models\OrderComplaintPhoto;
use App\Models\ShopOrder;
use App\Models\User;
use App\Notifications\OrderComplaintNotification;
use App\Repositories\OrderComplaintRepository;
use App\Repositories\ShopOrderRepository;
use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Throwable;

/**
 * Customers report a problem with a finished order (a stain, a missing item, ...), with photos.
 * The shop's owner and staff read the report, reply, and move it to resolved or rejected.
 */
class OrderComplaintService
{
    /** Same window as ratings: how long after the laundry came back a problem can be reported. */
    public const WINDOW_DAYS = OrderRatingService::WINDOW_DAYS;

    /** How many unanswered complaints one order can have at once. */
    public const MAX_ACTIVE_PER_ORDER = 2;

    public const MAX_PHOTOS = 3;

    private const CAP_MESSAGE = 'You already have open reports on this order. Please wait for the shop to respond.';

    private const DISK = 'private';

    public function __construct(
        private readonly OrderComplaintRepository $complaints,
        private readonly ShopOrderRepository $orders,
        private readonly ShopContext $shopContext,
        private readonly ActivityLogService $activityLog,
    ) {}

    // ── Customer side ─────────────────────────────────────────────────────────

    /**
     * @param  list<UploadedFile>  $photos
     *
     * @throws ModelNotFoundException when the order is not the customer's
     * @throws OrderComplaintException when a problem cannot be reported on the order
     */
    public function file(User $customer, string $paymentPublicId, string $type, string $description, array $photos): OrderComplaint
    {
        $order = $this->orders->findOwnedForComplaint($paymentPublicId, $customer->id)
            ?? throw (new ModelNotFoundException)->setModel(ShopOrder::class);

        $reason = $this->reasonCannotReport($order);
        if ($reason !== null) {
            throw new OrderComplaintException($reason);
        }

        $stored = [];

        try {
            $complaint = DB::transaction(function () use ($order, $type, $description, $photos, &$stored): OrderComplaint {
                // Lock the order so two simultaneous reports cannot both slip past the cap.
                $this->complaints->lockOrder($order);
                if ($this->complaints->countActiveForOrder($order) >= self::MAX_ACTIVE_PER_ORDER) {
                    throw new OrderComplaintException(self::CAP_MESSAGE);
                }

                $complaint = $this->complaints->createFor($order, $type, $description);
                $directory = "order-complaints/{$order->shop_id}/{$complaint->public_id}";

                foreach ($photos as $file) {
                    $path = $file->store($directory, self::DISK);
                    $stored[] = $path;
                    $this->complaints->addPhoto($complaint, $path);
                }

                return $complaint;
            });
        } catch (Throwable $exception) {
            Storage::disk(self::DISK)->delete($stored);

            throw $exception;
        }

        // The report is saved; a failed notification must not make the customer file it again.
        try {
            $order->shop?->owner?->notify(new OrderComplaintNotification($complaint->load('order'), 'filed'));
        } catch (Throwable $exception) {
            report($exception);
        }

        return $complaint;
    }

    /**
     * What the order page needs: whether a problem can be reported, and the customer's earlier reports.
     *
     * @return array{can_report: bool, reason: string|null, types: array<string, string>, items: list<array<string, mixed>>}
     */
    public function orderState(ShopOrder $order): array
    {
        $order->loadMissing('delivery');
        $reason = $this->reasonCannotReport($order);

        return [
            'can_report' => $reason === null,
            'reason' => $reason,
            'types' => OrderComplaint::TYPES,
            'items' => $this->complaints->forOrder($order)
                ->map(fn (OrderComplaint $complaint) => [
                    'public_id' => $complaint->public_id,
                    'type' => OrderComplaint::TYPES[$complaint->type] ?? $complaint->type,
                    'description' => $complaint->description,
                    'status' => $complaint->status,
                    'shop_reply' => $complaint->shop_reply,
                    'created_at' => $complaint->created_at->toIso8601String(),
                    'replied_at' => $complaint->replied_at?->toIso8601String(),
                    'photos' => $this->photoLinks($complaint, '/user/complaints/photos/'),
                ])
                ->all(),
        ];
    }

    /** The stored file the customer may open, or a not-found error. */
    public function photoPathForCustomer(User $customer, string $photoPublicId): string
    {
        return $this->existingPath($this->complaints->findPhotoForCustomer($photoPublicId, $customer->id));
    }

    // ── Shop side ─────────────────────────────────────────────────────────────

    public function shopPageData(User $user, array $filters): array
    {
        $shopId = $this->shopContext->shopIdFor($user);
        $prefix = $this->shopPrefix($user).'/operations/complaints/photos/';

        $page = $this->complaints->paginateForShop($shopId, $filters, (int) $filters['per_page'])
            ->through(fn (OrderComplaint $complaint) => [
                'public_id' => $complaint->public_id,
                'order_number' => $complaint->order_number,
                'customer_name' => $complaint->customer_name,
                'type' => $complaint->type,
                'type_label' => OrderComplaint::TYPES[$complaint->type] ?? $complaint->type,
                'description' => $complaint->description,
                'status' => $complaint->status,
                'shop_reply' => $complaint->shop_reply,
                'created_at' => $complaint->created_at->toIso8601String(),
                'replied_at' => $complaint->replied_at?->toIso8601String(),
                'photos' => $this->photoLinks($complaint, $prefix),
            ]);

        return [
            'stats' => $this->complaints->statsForShop($shopId),
            'complaints' => $page,
            'types' => OrderComplaint::TYPES,
            'filters' => $filters,
        ];
    }

    /**
     * @throws ModelNotFoundException when the complaint is not in the user's shop
     * @throws OrderComplaintException when the complaint is already closed
     */
    public function respond(User $user, string $complaintPublicId, string $status, ?string $reply): OrderComplaint
    {
        $shopId = $this->shopContext->shopIdFor($user);
        $complaint = $this->complaints->findForShop($complaintPublicId, $shopId)
            ?? throw (new ModelNotFoundException)->setModel(OrderComplaint::class);

        DB::transaction(function () use ($user, $complaint, $shopId, $status, $reply): void {
            // Re-check under a row lock so two people answering at once cannot both succeed.
            $complaint = $this->complaints->lockForUpdate($complaint);

            if (! $complaint->isActive()) {
                throw new OrderComplaintException('This report is already closed and can no longer be changed.');
            }

            $this->complaints->respond($complaint, [
                'status' => $status,
                // A blank reply keeps what the shop already told the customer.
                'shop_reply' => $reply ?? $complaint->shop_reply,
                'replied_by' => $user->id,
                'replied_at' => now(),
                'resolved_at' => $status === 'resolved' ? now() : null,
            ]);

            $this->activityLog->log(
                $complaint,
                "Responded to a customer complaint: {$status}",
                ['status' => $status],
                $shopId,
                'Operations',
            );
        });

        $complaint->refresh();

        try {
            $complaint->customer?->notify(new OrderComplaintNotification($complaint->load('order.shop'), 'responded'));
        } catch (Throwable $exception) {
            report($exception);
        }

        return $complaint;
    }

    /** The stored file a shop user may open, or a not-found error. */
    public function photoPathForShop(User $user, string $photoPublicId): string
    {
        return $this->existingPath($this->complaints->findPhotoForShop(
            $photoPublicId,
            $this->shopContext->shopIdFor($user),
        ));
    }

    // ── Rules ─────────────────────────────────────────────────────────────────

    /** Why a problem cannot be reported on this order right now, or null when it can. */
    private function reasonCannotReport(ShopOrder $order): ?string
    {
        if ($order->status !== 'completed') {
            return 'You can report a problem once your order is completed.';
        }

        if ($order->delivery !== null && $order->delivery->status !== 'delivered') {
            return 'You can report a problem once your order has been delivered.';
        }

        $completedAt = $this->completedAt($order);

        if ($completedAt === null || $completedAt->lessThan(now()->subDays(self::WINDOW_DAYS))) {
            return 'The period to report a problem with this order has ended ('.self::WINDOW_DAYS.' days).';
        }

        if ($this->complaints->countActiveForOrder($order) >= self::MAX_ACTIVE_PER_ORDER) {
            return self::CAP_MESSAGE;
        }

        return null;
    }

    private function completedAt(ShopOrder $order): ?CarbonInterface
    {
        return $order->delivery?->delivered_at ?? $order->completed_at ?? $order->updated_at;
    }

    // ── Helpers ───────────────────────────────────────────────────────────────

    private function shopPrefix(User $user): string
    {
        return $user->isOwner() ? '/shop' : '/staff';
    }

    /** @return list<array{public_id: string, url: string}> */
    private function photoLinks(OrderComplaint $complaint, string $prefix): array
    {
        return $complaint->photos
            ->map(fn (OrderComplaintPhoto $photo) => [
                'public_id' => $photo->public_id,
                'url' => $prefix.$photo->public_id,
            ])
            ->values()
            ->all();
    }

    // A record out of the caller's scope is reported exactly like a missing one.
    private function existingPath(?OrderComplaintPhoto $photo): string
    {
        if ($photo === null || ! Storage::disk(self::DISK)->exists($photo->path)) {
            throw (new ModelNotFoundException)->setModel(OrderComplaintPhoto::class);
        }

        return $photo->path;
    }
}
