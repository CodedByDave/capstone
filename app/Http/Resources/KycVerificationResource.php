<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin \App\Models\KycVerification */
class KycVerificationResource extends JsonResource
{
    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        return [
            'public_id' => $this->public_id,
            'status' => $this->status,
            'shop_name' => $this->shop_name,
            'owner_name' => $this->user?->name,
            'email' => $this->user?->email,
            'block_street' => $this->block_street,
            'barangay' => $this->barangay,
            'municipality' => $this->municipality,
            'postal_code' => $this->postal_code,
            'documents' => collect(['bir', 'dti', 'mayors', 'sanitary'])
                ->filter(fn ($doc) => $this->{"kyc_{$doc}"})
                ->mapWithKeys(fn ($doc) => [$doc => $this->{"kyc_{$doc}"}])
                ->all(),
            'rejection_reason' => $this->rejection_reason,
            'flags' => $this->kyc_flags ?? [],
            'reviewed_by' => $this->reviewer?->name,
            'reviewed_at' => $this->reviewed_at?->format('M d, Y h:i A'),
            'created_at' => $this->created_at?->format('M d, Y h:i A'),
        ];
    }
}
