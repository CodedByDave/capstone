<?php

namespace App\Services;

use App\Models\User;
use App\Repositories\KycVerificationRepository;

/**
 * What Shop Settings shows for the shop's location: the address OCR read from the
 * business permits, and whether it matched the address the owner registered.
 */
class ShopLocationService
{
    public function __construct(
        private readonly KycVerificationRepository $verifications,
        private readonly KycReviewService $review,
    ) {}

    /** @return array{verified: bool, permit_address: ?string, matched: bool} */
    public function verificationFor(User $owner): array
    {
        $verification = $this->verifications->latestApprovedForUser($owner->id);

        if (! $verification) {
            return ['verified' => false, 'permit_address' => null, 'matched' => false];
        }

        $row = collect($this->review->rows($verification))->firstWhere('key', 'registered_address');

        return [
            'verified' => true,
            'permit_address' => $row['ocr'] ?? null,
            'matched' => ($row['status'] ?? null) === 'match',
        ];
    }
}
