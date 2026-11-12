<?php

namespace App\Services;

use App\Exceptions\OrderSubmissionException;
use App\Models\KycVerification;
use App\Models\User;
use App\Notifications\KycVerificationApprovedNotification;
use App\Notifications\KycVerificationRejectedNotification;
use App\Repositories\KycVerificationRepository;
use App\Repositories\ShopRepository;
use App\Repositories\UserRepository;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Throwable;

/**
 * Owner verification: the owner uploads permits, OCR reads them, the owner
 * confirms the result, and an admin approves or rejects. Approval activates
 * the free trial automatically when the owner is eligible.
 */
class KycVerificationService
{
    private const DOCS = ['bir', 'dti', 'mayors', 'sanitary'];

    public function __construct(
        private readonly KycVerificationRepository $verifications,
        private readonly ShopRepository $shops,
        private readonly UserRepository $users,
        private readonly KycDocumentScanService $scanner,
        private readonly BusinessAgreementService $agreements,
        private readonly TrialService $trials,
        private readonly KycDocumentVault $vault,
        private readonly KycReviewService $review,
    ) {}

    /** An owner is verified by an approved verification, or by an earlier admin-approved plan. */
    public function isVerified(int $userId): bool
    {
        return $this->verifications->isVerifiedOwner($userId);
    }

    /**
     * State for the onboarding screen.
     *
     * @return array<string, mixed>
     */
    public function onboardingData(User $user): array
    {
        $latest = $this->verifications->latestForUser($user->id);
        $verified = $this->isVerified($user->id);
        $shop = $this->shops->findByOwnerId($user->id);

        return [
            'status' => $verified ? 'approved' : ($latest?->status ?? 'none'),
            'verified' => $verified,
            'submitted_at' => $latest?->created_at?->format('M d, Y'),
            'previous' => ! $verified && $latest?->status === KycVerification::STATUS_REJECTED
                ? $this->previousSubmissionData($latest)
                : null,
            'registration' => [
                'shop_name' => $shop?->shop_name ?? '',
                'block_street' => $shop?->block_street ?? '',
                'barangay' => $shop?->barangay ?? '',
                'municipality' => $shop?->municipality ?? '',
                'postal_code' => $shop?->postal_code ?? '',
            ],
        ];
    }

    /**
     * @throws OrderSubmissionException when a verification is already pending or approved
     */
    public function submit(User $user, array $data, ?string $ipAddress, ?string $userAgent): KycVerification
    {
        return DB::transaction(function () use ($user, $data, $ipAddress, $userAgent) {
            $this->users->lockForOrderSubmission($user->id);

            if ($this->verifications->hasWithStatus($user->id, KycVerification::STATUS_PENDING)) {
                throw new OrderSubmissionException('Your verification is already under review.');
            }
            if ($this->isVerified($user->id)) {
                throw new OrderSubmissionException('Your business is already verified.');
            }

            $shop = $this->shops->findByOwnerId($user->id);
            if (! $shop) {
                throw new OrderSubmissionException('A business profile is required before verification.');
            }

            // The agreement is signed here so the trial can start the moment an admin approves.
            $this->agreements->acceptStandalone($user, $data + [
                'signer_name' => $user->name,
                'signer_role' => 'Owner',
            ], $ipAddress, $userAgent);

            $verification = $this->verifications->create([
                'user_id' => $user->id,
                'status' => KycVerification::STATUS_PENDING,
                'shop_name' => $shop->shop_name,
                'phone' => $shop->phone,
                'block_street' => $shop->block_street,
                'municipality' => $shop->municipality,
                'barangay' => $shop->barangay,
                'postal_code' => $shop->postal_code,
                'bir_expiry_date' => $data['bir_expiry_date'] ?? null,
                'dti_expiry_date' => $data['dti_expiry_date'] ?? null,
                'mayors_expiry_date' => $data['mayors_expiry_date'] ?? null,
                'sanitary_expiry_date' => $data['sanitary_expiry_date'] ?? null,
            ]);

            $previous = $this->verifications->latestRejectedForUser($user->id);
            $reuse = (array) ($data['reuse_kyc'] ?? []);
            $paths = [];
            $ocr = [];

            foreach (self::DOCS as $doc) {
                $column = "kyc_{$doc}";
                $upload = $data[$column] ?? null;

                if ($upload) {
                    // Encrypted at rest; only the Super Admin's document viewer decrypts it.
                    $paths[$column] = $this->vault->store($upload, "kyc-verifications/{$verification->id}");
                    // What OCR really read for this exact file, recalled server-side.
                    $ocr[$doc] = $this->scanner->recall($user->id, $upload);
                } elseif (in_array($doc, $reuse, true) && $previous && $this->canReuse($previous, $doc)) {
                    $paths[$column] = $this->vault->copy($previous->{$column}, "kyc-verifications/{$verification->id}");
                    $ocr[$doc] = $previous->kyc_ocr_data[$doc] ?? null;
                }
            }

            foreach (['bir', 'dti', 'mayors'] as $required) {
                if (! isset($paths["kyc_{$required}"])) {
                    throw new OrderSubmissionException('Please upload all required business documents.');
                }
            }

            $confirmed = $data['kyc_confirmed'] ?? [];
            $this->verifications->updateDocuments($verification, $paths + [
                'kyc_ocr_data' => array_filter($ocr) ?: null,
                'kyc_confirmed_data' => [
                    'tin' => trim((string) ($confirmed['tin'] ?? '')),
                    'tax_type' => $confirmed['tax_type'] ?? null,
                    'registered_name' => trim((string) ($confirmed['registered_name'] ?? '')),
                    'registered_address' => trim((string) ($confirmed['registered_address'] ?? '')),
                ],
            ]);

            return $verification->refresh();
        });
    }

    /**
     * Approve, record the permit dates on the shop, and start the free trial if eligible.
     *
     * @throws ValidationException when the verification is no longer pending
     */
    public function approve(User $admin, KycVerification $verification): KycVerification
    {
        $approved = DB::transaction(function () use ($admin, $verification) {
            $locked = $this->verifications->findForReview($verification->id);
            $this->assertPending($locked);
            $this->assertAllDetailsMatch($locked);

            $this->verifications->markApproved($locked, $admin->id);
            $this->shops->applyVerifiedPermits($locked->user_id, [
                'bir_expiry_date' => $locked->bir_expiry_date,
                'dti_expiry_date' => $locked->dti_expiry_date,
                'mayors_expiry_date' => $locked->mayors_expiry_date,
                'sanitary_expiry_date' => $locked->sanitary_expiry_date,
                'tax_type' => $locked->kyc_confirmed_data['tax_type'] ?? null,
            ]);

            return $locked->refresh()->load('user');
        });

        $trialStarted = $this->startTrialIfEligible($approved);
        $approved->user?->notify(new KycVerificationApprovedNotification($approved, $trialStarted));

        return $approved;
    }

    /**
     * @param  array<int, string>  $flags  fields/documents the owner must redo
     *
     * @throws ValidationException when the verification is no longer pending
     */
    public function reject(User $admin, KycVerification $verification, string $reason, array $flags = []): KycVerification
    {
        $rejected = DB::transaction(function () use ($admin, $verification, $reason, $flags) {
            $locked = $this->verifications->findForReview($verification->id);
            $this->assertPending($locked);

            $this->verifications->markRejected($locked, $admin->id, $reason, array_values(array_unique($flags)));

            return $locked->refresh()->load('user');
        });

        $rejected->user?->notify(new KycVerificationRejectedNotification($rejected));

        return $rejected;
    }

    public function paginateForAdmin(array $filters): LengthAwarePaginator
    {
        return $this->verifications->paginateForAdmin($filters);
    }

    /** @return array<string, int> */
    public function statusCounts(): array
    {
        return $this->verifications->statusCounts();
    }

    /**
     * Documents and dates for the admin shop page. Returns null when the owner never
     * submitted a verification (older shops keep their documents on the order).
     *
     * @return array{files: array<string, ?string>, expiry: array<string, ?string>, submission: array{status: string, submitted_at: mixed, reference: string}}|null
     */
    public function permitSnapshotForOwner(int $ownerId): ?array
    {
        $verification = $this->verifications->latestActiveForUser($ownerId);
        if (! $verification) {
            return null;
        }

        $docs = ['bir', 'dti', 'mayors', 'sanitary'];

        return [
            'files' => collect($docs)->mapWithKeys(fn ($d) => [$d => $verification->{"kyc_{$d}"}])->all(),
            'expiry' => collect($docs)->mapWithKeys(fn ($d) => [
                "{$d}_expiry_date" => $verification->{"{$d}_expiry_date"}?->toDateString(),
            ])->all(),
            'submission' => [
                'status' => $verification->status,
                'submitted_at' => $verification->created_at,
                'reference' => $verification->public_id,
            ],
        ];
    }

    public function loadForAdmin(KycVerification $verification): KycVerification
    {
        return $verification->load(['user:id,name,email', 'reviewer:id,name']);
    }

    /** Approval needs every detail the owner confirmed to match what OCR read from the documents. */
    private function assertAllDetailsMatch(KycVerification $verification): void
    {
        $blockers = $this->review->approvalBlockers($verification);

        if ($blockers !== []) {
            throw ValidationException::withMessages([
                'verification' => 'This application cannot be approved until every detail matches. '
                    .implode(' ', $blockers)
                    .' Reject it and flag these so the owner can correct them.',
            ]);
        }
    }

    private function assertPending(KycVerification $verification): void
    {
        if ($verification->status !== KycVerification::STATUS_PENDING) {
            throw ValidationException::withMessages([
                'verification' => 'This verification is already '.$verification->status.'. Refresh the page.',
            ]);
        }
    }

    private function startTrialIfEligible(KycVerification $verification): bool
    {
        $user = $verification->user;
        if (! $user) {
            return false;
        }

        $availability = $this->trials->availability($user);
        if ($availability['has_open_application'] || $availability['has_used_trial']) {
            return false;
        }

        try {
            $this->trials->startForVerifiedOwner($user, [
                'shop_name' => $verification->shop_name,
                'phone' => $verification->phone,
                'block_street' => $verification->block_street,
                'municipality' => $verification->municipality,
                'barangay' => $verification->barangay,
                'postal_code' => $verification->postal_code,
            ]);

            return true;
        } catch (Throwable $e) {
            // The owner is still verified and can start the trial from the welcome screen.
            Log::warning('Auto-starting the free trial after KYC approval failed', [
                'user_id' => $user->id,
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }

    private function canReuse(KycVerification $previous, string $doc): bool
    {
        $path = $previous->{"kyc_{$doc}"};

        return $path
            && ! in_array("kyc_{$doc}", $previous->kyc_flags ?? [], true)
            && $this->vault->exists($path);
    }

    /**
     * What the owner may keep from a rejected verification: everything not flagged.
     *
     * @return array<string, mixed>
     */
    private function previousSubmissionData(KycVerification $previous): array
    {
        $flags = $previous->kyc_flags ?? [];
        $keep = fn (string $key) => ! in_array($key, $flags, true);

        return [
            'reason' => $previous->rejection_reason,
            'flags' => $flags,
            'reusable_docs' => array_values(array_filter(self::DOCS, fn ($d) => $this->canReuse($previous, $d))),
            'expiry' => collect(self::DOCS)
                ->filter(fn ($d) => $keep("{$d}_expiry_date") && $previous->{"{$d}_expiry_date"})
                ->mapWithKeys(fn ($d) => [$d => $previous->{"{$d}_expiry_date"}->toDateString()])
                ->all(),
            'confirmed' => collect($previous->kyc_confirmed_data ?? [])
                ->filter(fn ($v, $k) => $keep($k))
                ->all(),
        ];
    }
}
