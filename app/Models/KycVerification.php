<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

/**
 * A shop owner's identity/business verification: uploaded permits, what OCR
 * read from them, what the owner confirmed, and the admin's decision.
 */
class KycVerification extends Model
{
    public const STATUS_PENDING = 'pending';

    public const STATUS_APPROVED = 'approved';

    public const STATUS_REJECTED = 'rejected';

    protected $fillable = [
        'user_id', 'status',
        'shop_name', 'phone', 'block_street', 'municipality', 'barangay', 'postal_code',
        'kyc_bir', 'kyc_dti', 'kyc_mayors', 'kyc_sanitary',
        'bir_expiry_date', 'dti_expiry_date', 'mayors_expiry_date', 'sanitary_expiry_date',
        'kyc_ocr_data', 'kyc_confirmed_data', 'kyc_flags', 'rejection_reason',
        'reviewed_by', 'reviewed_at',
    ];

    protected $casts = [
        'bir_expiry_date' => 'date',
        'dti_expiry_date' => 'date',
        'mayors_expiry_date' => 'date',
        'sanitary_expiry_date' => 'date',
        'kyc_ocr_data' => 'array',
        'kyc_confirmed_data' => 'array',
        'kyc_flags' => 'array',
        'reviewed_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::creating(function (self $verification) {
            $verification->public_id ??= (string) Str::ulid();
        });
    }

    public function getRouteKeyName(): string
    {
        return 'public_id';
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }
}
