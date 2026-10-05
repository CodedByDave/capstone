<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Str;

class BusinessAgreementAcceptance extends Model
{
    protected $fillable = [
        'public_id',
        'business_agreement_id',
        'shop_id',
        'user_id',
        'order_id',
        'business_name',
        'signer_name',
        'signer_role',
        'signature_method',
        'signature_path',
        'accepted_at',
        'ip_address',
        'user_agent',
        'content_hash',
    ];

    protected function casts(): array
    {
        return ['accepted_at' => 'datetime'];
    }

    protected static function booted(): void
    {
        static::creating(function (BusinessAgreementAcceptance $acceptance) {
            $acceptance->public_id ??= (string) Str::ulid();
        });
    }

    public function agreement(): BelongsTo
    {
        return $this->belongsTo(BusinessAgreement::class, 'business_agreement_id');
    }

    public function shop(): BelongsTo
    {
        return $this->belongsTo(Shop::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function platformSignature(): HasOne
    {
        return $this->hasOne(
            BusinessAgreementPlatformSignature::class,
            'business_agreement_acceptance_id',
        );
    }
}
