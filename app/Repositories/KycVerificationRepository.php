<?php

namespace App\Repositories;

use App\Models\KycVerification;
use App\Models\Order;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;

class KycVerificationRepository extends Repository
{
    private const SORTABLE = [
        'created_at' => 'kyc_verifications.created_at',
        'shop' => 'kyc_verifications.shop_name',
        'status' => 'kyc_verifications.status',
    ];

    public function __construct(KycVerification $verification)
    {
        parent::__construct($verification);
    }

    public function latestForUser(int $userId): ?KycVerification
    {
        return KycVerification::query()->where('user_id', $userId)->latest('id')->first();
    }

    public function latestRejectedForUser(int $userId): ?KycVerification
    {
        return KycVerification::query()
            ->where('user_id', $userId)
            ->where('status', KycVerification::STATUS_REJECTED)
            ->latest('id')
            ->first();
    }

    /** The verification the admin should see on the shop page: under review or approved, never rejected. */
    public function latestActiveForUser(int $userId): ?KycVerification
    {
        return KycVerification::query()
            ->where('user_id', $userId)
            ->whereIn('status', [KycVerification::STATUS_PENDING, KycVerification::STATUS_APPROVED])
            ->latest('id')
            ->first();
    }

    public function latestApprovedForUser(int $userId): ?KycVerification
    {
        return KycVerification::query()
            ->where('user_id', $userId)
            ->where('status', KycVerification::STATUS_APPROVED)
            ->latest('id')
            ->first();
    }

    /** Verified by an approved verification, or by an admin-approved plan application before this feature existed. */
    public function isVerifiedOwner(int $userId): bool
    {
        return $this->hasWithStatus($userId, KycVerification::STATUS_APPROVED)
            || Order::query()
                ->where('user_id', $userId)
                ->whereIn('status', ['approved', 'paid', 'expired'])
                ->where('is_trial', false)
                ->exists();
    }

    public function hasWithStatus(int $userId, string $status): bool
    {
        return KycVerification::query()->where('user_id', $userId)->where('status', $status)->exists();
    }

    public function findForReview(int $id): KycVerification
    {
        return KycVerification::query()->with('user')->lockForUpdate()->findOrFail($id);
    }

    public function create(array $data): KycVerification
    {
        return KycVerification::create($data);
    }

    public function updateDocuments(KycVerification $verification, array $attributes): void
    {
        $verification->update($attributes);
    }

    public function markApproved(KycVerification $verification, int $reviewerId): void
    {
        $verification->update([
            'status' => KycVerification::STATUS_APPROVED,
            'reviewed_by' => $reviewerId,
            'reviewed_at' => now(),
            'rejection_reason' => null,
            'kyc_flags' => null,
        ]);
    }

    public function markRejected(KycVerification $verification, int $reviewerId, string $reason, array $flags): void
    {
        $verification->update([
            'status' => KycVerification::STATUS_REJECTED,
            'reviewed_by' => $reviewerId,
            'reviewed_at' => now(),
            'rejection_reason' => $reason,
            'kyc_flags' => $flags ?: null,
        ]);
    }

    /** @param array{search?: ?string, status?: ?string, sort_by: string, sort_direction: string, per_page: int} $filters */
    public function paginateForAdmin(array $filters): LengthAwarePaginator
    {
        return KycVerification::query()
            ->with('user:id,name,email')
            ->when($filters['status'] ?? null, fn (Builder $q, string $status) => $q->where('status', $status))
            ->when($filters['search'] ?? null, function (Builder $q, string $search) {
                $like = '%'.$search.'%';
                $q->where(fn (Builder $w) => $w
                    ->where('shop_name', 'like', $like)
                    ->orWhereHas('user', fn (Builder $u) => $u->where('name', 'like', $like)->orWhere('email', 'like', $like)));
            })
            ->orderBy(self::SORTABLE[$filters['sort_by']] ?? self::SORTABLE['created_at'], $filters['sort_direction'])
            ->orderByDesc('kyc_verifications.id')
            ->paginate($filters['per_page'])
            ->withQueryString();
    }

    /** @return array<string, int> */
    public function statusCounts(): array
    {
        $counts = KycVerification::query()
            ->selectRaw('status, count(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        return [
            'all' => (int) $counts->sum(),
            'pending' => (int) ($counts['pending'] ?? 0),
            'approved' => (int) ($counts['approved'] ?? 0),
            'rejected' => (int) ($counts['rejected'] ?? 0),
        ];
    }
}
