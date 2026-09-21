<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Shop extends Model
{
    use SoftDeletes;

    protected static function booted(): void
    {
        static::creating(function (Shop $shop) {
            $shop->public_id ??= (string) Str::ulid();
        });
    }

    protected $fillable = [
        'public_id',
        'owner_id',
        'shop_name',
        'branch_name',
        'phone',
        'block_street',
        'municipality',
        'barangay',
        'postal_code',
        'latitude',
        'longitude',
        'cover_photo',
        'gcash_qr',
        'maya_qr',
        'paymongo_secret_key',
        'paymongo_public_key',
        'status',
        'bir_expiry_date',
        'mayors_expiry_date',
        'dti_expiry_date',
        'sanitary_expiry_date',
        'last_activity_at',
        'disable_reason',
        'deduct_sss',
        'deduct_philhealth',
        'deduct_pagibig',
        'deduct_withholding_tax',
    ];

    protected $casts = [
        'deduct_sss' => 'boolean',
        'deduct_philhealth' => 'boolean',
        'deduct_pagibig' => 'boolean',
        'deduct_withholding_tax' => 'boolean',
        'latitude' => 'float',
        'longitude' => 'float',
        'bir_expiry_date' => 'date',
        'mayors_expiry_date' => 'date',
        'dti_expiry_date' => 'date',
        'sanitary_expiry_date' => 'date',
        'last_activity_at' => 'datetime',
        'paymongo_secret_key' => 'encrypted',
    ];

    public function hasPaymongo(): bool
    {
        return ! empty($this->paymongo_secret_key) && ! empty($this->paymongo_public_key);
    }

    public function latestOrder(): HasOne
    {
        return $this->hasOne(Order::class, 'user_id', 'owner_id')
            ->whereIn('status', ['paid', 'approved'])
            ->latestOfMany();
    }

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function order(): HasOne
    {
        return $this->hasOne(Order::class, 'user_id', 'owner_id');
    }

    public function employees(): HasMany
    {
        return $this->hasMany(Employee::class);
    }

    public function services(): HasMany
    {
        return $this->hasMany(ShopService::class);
    }

    public function shopOrders(): HasMany
    {
        return $this->hasMany(ShopOrder::class);
    }

    public function getBranchNames(): array
    {
        $branches = $this->employees()
            ->whereNotNull('branch_name')
            ->distinct()
            ->pluck('branch_name')
            ->toArray();

        // Include the shop's own branch name if set
        if ($this->branch_name && ! in_array($this->branch_name, $branches)) {
            array_unshift($branches, $this->branch_name);
        }

        return $branches;
    }
}
