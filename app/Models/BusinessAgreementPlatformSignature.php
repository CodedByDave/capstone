<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class BusinessAgreementPlatformSignature extends Model
{
    protected $fillable = [
        'public_id',
        'business_agreement_acceptance_id',
        'admin_user_id',
        'signer_name',
        'signer_role',
        'signature_method',
        'signature_path',
        'signed_at',
        'ip_address',
        'user_agent',
    ];

    protected function casts(): array
    {
        return ['signed_at' => 'datetime'];
    }

    protected static function booted(): void
    {
        static::creating(function (BusinessAgreementPlatformSignature $signature) {
            $signature->public_id ??= (string) Str::ulid();
        });
    }

    public function acceptance(): BelongsTo
    {
        return $this->belongsTo(
            BusinessAgreementAcceptance::class,
            'business_agreement_acceptance_id',
        );
    }

    public function admin(): BelongsTo
    {
        return $this->belongsTo(User::class, 'admin_user_id');
    }
}
