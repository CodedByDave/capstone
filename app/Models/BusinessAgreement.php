<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class BusinessAgreement extends Model
{
    protected $fillable = [
        'public_id',
        'title',
        'version',
        'content',
        'content_hash',
        'effective_at',
        'published_at',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'effective_at' => 'datetime',
            'published_at' => 'datetime',
            'is_active' => 'boolean',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (BusinessAgreement $agreement) {
            $agreement->public_id ??= (string) Str::ulid();
            $agreement->content_hash ??= hash('sha256', $agreement->content);
        });
    }

    public function acceptances(): HasMany
    {
        return $this->hasMany(BusinessAgreementAcceptance::class);
    }
}
