<?php

namespace App\Services;

use App\Exceptions\PlatformRatingException;
use App\Models\PlatformRating;
use App\Models\Shop;
use App\Models\User;
use App\Repositories\PlatformRatingRepository;
use App\Repositories\ShopOrderRepository;
use App\Repositories\ShopRepository;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\DB;

/**
 * Shop owners rate the LaundryHub platform itself. The owner is asked after the shop completes
 * an order, at most once a month; the super admin reads the results.
 */
class PlatformRatingService
{
    /** Minimum days between two prompts to the same shop. */
    public const PROMPT_INTERVAL_DAYS = 30;

    /** A completed order within this many days counts as "just completed an order". */
    public const RECENT_ORDER_DAYS = 14;

    /** The owner route group the rating endpoints live in, for the platform permission check. */
    private const OWNER_PATH = 'shop/platform-rating';

    public function __construct(
        private readonly PlatformRatingRepository $ratings,
        private readonly ShopRepository $shops,
        private readonly ShopOrderRepository $orders,
        private readonly PlatformRoleService $platformRoles,
    ) {}

    /** Whether to show the owner the rate-the-platform popup right now. */
    public function shouldPrompt(User $user): bool
    {
        if (! $user->isOwner()) {
            return false;
        }

        $shop = $this->shops->findByOwnerId($user->id);

        // The cheap checks run first; the permission check only matters once a prompt is due. An
        // owner whose platform role cannot use the endpoints is never shown a popup that would fail.
        return $shop !== null
            && $this->canPrompt($shop)
            && $this->platformRoles->canAccessOwnerPath(self::OWNER_PATH);
    }

    /**
     * @throws PlatformRatingException when the owner was asked or rated recently
     */
    public function submit(User $owner, int $rating, ?string $comment): PlatformRating
    {
        return DB::transaction(function () use ($owner, $rating, $comment): PlatformRating {
            // Lock the shop row so two simultaneous submits cannot both pass the monthly check.
            $shop = $this->shops->lockForOwner($owner->id)
                ?? throw (new ModelNotFoundException)->setModel(Shop::class);

            if (! $this->canPrompt($shop)) {
                throw new PlatformRatingException('Thanks, you have already rated LaundryHub recently.');
            }

            $created = $this->ratings->createFor($shop->id, $owner->id, $rating, $comment);
            $this->shops->markPlatformRatingPrompted($shop);

            return $created;
        });
    }

    /** "Maybe later": do not ask again for a month. Ignored when no prompt was due. */
    public function dismiss(User $owner): void
    {
        $shop = $this->ownerShop($owner);

        if ($this->canPrompt($shop)) {
            $this->shops->markPlatformRatingPrompted($shop);
        }
    }

    /** The super admin's Platform Ratings page: totals plus a filtered, sorted page of ratings. */
    public function adminPageData(array $filters): array
    {
        $page = $this->ratings->paginateForAdmin($filters, (int) $filters['per_page'])
            ->through(fn (PlatformRating $rating) => [
                'public_id' => $rating->public_id,
                'shop_name' => $rating->shop_name,
                'owner_name' => $rating->owner_name,
                'owner_email' => $rating->owner_email,
                'rating' => $rating->rating,
                'comment' => $rating->comment,
                'created_at' => $rating->created_at->toIso8601String(),
            ]);

        return [
            'stats' => $this->ratings->stats(),
            'ratings' => $page,
            'filters' => $filters,
        ];
    }

    private function canPrompt(Shop $shop): bool
    {
        if ($shop->setup_completed_at === null) {
            return false;
        }

        $last = $shop->platform_rating_prompted_at;

        if ($last !== null && $last->greaterThan(now()->subDays(self::PROMPT_INTERVAL_DAYS))) {
            return false;
        }

        return $this->orders->hasCompletedSince($shop->id, now()->subDays(self::RECENT_ORDER_DAYS));
    }

    private function ownerShop(User $owner): Shop
    {
        return $this->shops->findByOwnerId($owner->id)
            ?? throw (new ModelNotFoundException)->setModel(Shop::class);
    }
}
