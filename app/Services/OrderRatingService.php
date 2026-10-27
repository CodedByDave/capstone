<?php

namespace App\Services;

use App\Exceptions\OrderRatingException;
use App\Models\OrderRating;
use App\Models\ShopOrder;
use App\Models\User;
use App\Repositories\OrderRatingRepository;
use App\Repositories\ShopOrderRepository;
use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Support\Facades\DB;

/**
 * Customers rate a shop once their laundry is done: either completed at the shop, or
 * delivered. One final rating per order, within a limited time after completion.
 */
class OrderRatingService
{
    /** How long after completion a customer can still rate the order. */
    public const WINDOW_DAYS = 30;

    public function __construct(
        private readonly OrderRatingRepository $ratings,
        private readonly ShopOrderRepository $orders,
        private readonly ShopContext $shopContext,
    ) {}

    // ── Customer side ─────────────────────────────────────────────────────────

    /**
     * @throws ModelNotFoundException when the order is not the customer's
     * @throws OrderRatingException when the order cannot be rated
     */
    public function submit(User $customer, string $paymentPublicId, int $rating, ?string $comment): OrderRating
    {
        $order = $this->orders->findOwnedForRating($paymentPublicId, $customer->id)
            ?? throw (new ModelNotFoundException)->setModel(ShopOrder::class);

        $this->assertRatable($order);

        try {
            return DB::transaction(fn () => $this->ratings->createFor($order, $rating, $comment));
        } catch (UniqueConstraintViolationException) {
            // A double submit raced past the check above; the unique index is the real guard.
            throw new OrderRatingException('You have already rated this order.');
        }
    }

    /** @return array{can_rate: bool, rating: array{stars: int, comment: string|null, rated_at: string}|null} */
    public function orderState(ShopOrder $order): array
    {
        $order->loadMissing(['delivery', 'rating']);

        return [
            'can_rate' => $this->isRatable($order),
            'rating' => $order->rating ? [
                'stars' => $order->rating->rating,
                'comment' => $order->rating->comment,
                'rated_at' => $order->rating->created_at->toIso8601String(),
            ] : null,
        ];
    }

    /**
     * The customer's most recent order still waiting for a rating, for the dashboard prompt.
     *
     * @return array{payment_public_id: string, order_number: string, shop_name: string}|null
     */
    public function promptFor(User $customer): ?array
    {
        $order = $this->orders->completedWithoutRating($customer->id)
            ->first(fn (ShopOrder $candidate) => $this->isRatable($candidate));

        return $order ? [
            'payment_public_id' => $order->payment_public_id,
            'order_number' => $order->order_number,
            'shop_name' => $order->shop?->shop_name ?? 'the shop',
        ] : null;
    }

    public function isRatable(ShopOrder $order): bool
    {
        return $this->reasonNotRatable($order) === null;
    }

    // ── Shop side ─────────────────────────────────────────────────────────────

    /**
     * Average and count per shop, in one query, for the customer-facing shop pages.
     *
     * @param  list<int>  $shopIds
     * @return array<int, array{average: float, count: int}>
     */
    public function summariesFor(array $shopIds): array
    {
        return $this->ratings->summariesForShops($shopIds);
    }

    /** The owner's and staff's Ratings page: totals plus a filtered, sorted page of ratings. */
    public function shopPageData(User $user, array $filters): array
    {
        $shopId = $this->shopContext->shopIdFor($user);
        $page = $this->ratings->paginateForShop($shopId, $filters, (int) $filters['per_page'])
            ->through(fn (OrderRating $rating) => [
                'order_number' => $rating->order_number,
                'customer_name' => $rating->customer_name,
                'rating' => $rating->rating,
                'comment' => $rating->comment,
                'created_at' => $rating->created_at->toIso8601String(),
            ]);

        return [
            'summary' => $this->ratings->summaryForShop($shopId),
            'ratings' => $page,
            'filters' => $filters,
        ];
    }

    // ── Rules ─────────────────────────────────────────────────────────────────

    private function assertRatable(ShopOrder $order): void
    {
        $reason = $this->reasonNotRatable($order);

        if ($reason !== null) {
            throw new OrderRatingException($reason);
        }
    }

    /** Why the order cannot be rated right now, or null when it can. */
    private function reasonNotRatable(ShopOrder $order): ?string
    {
        $order->loadMissing(['delivery', 'rating']);

        if ($order->rating !== null) {
            return 'You have already rated this order.';
        }

        if (! in_array($order->status, ShopOrder::FINISHED_STATUSES, true)) {
            return 'You can rate an order once it is completed.';
        }

        if ($order->delivery !== null && $order->delivery->status !== 'delivered') {
            return 'You can rate this order once it has been delivered.';
        }

        $completedAt = $this->completedAt($order);

        if ($completedAt === null || $completedAt->lessThan(now()->subDays(self::WINDOW_DAYS))) {
            return 'The rating period for this order has ended.';
        }

        return null;
    }

    /** When the customer actually got the laundry: the delivery time, else when the order was completed. */
    private function completedAt(ShopOrder $order): ?CarbonInterface
    {
        return $order->delivery?->delivered_at ?? $order->completed_at ?? $order->updated_at;
    }
}
