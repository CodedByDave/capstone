<?php

namespace App\Models;

use App\Casts\PhilippinePhoneCast;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Shop extends Model
{
    use SoftDeletes;

    public const LOCATION_SINGLE = 'single';

    public const LOCATION_MULTIPLE = 'multiple';

    public const OWNERSHIP_INDEPENDENT = 'independent';

    public const OWNERSHIP_COMPANY = 'company_owned';

    public const OWNERSHIP_FRANCHISEE = 'franchisee';

    public const DELIVERY_FEE_FIXED = 'fixed';

    public const DELIVERY_FEE_PER_KM = 'per_km';

    public const DELIVERY_FEE_MODES = [self::DELIVERY_FEE_FIXED, self::DELIVERY_FEE_PER_KM];

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
        'ownership_type',
        'franchise_brand_name',
        'franchisor_name',
        'location_mode',
        'offers_pickup',
        'offers_delivery',
        'delivery_fee_mode',
        'delivery_fixed_fee',
        'delivery_base_fee',
        'delivery_per_km_rate',
        'delivery_free_km',
        'delivery_min_fee',
        'delivery_max_km',
        'delivery_pricing_set_at',
        'platform_rating_prompted_at',
        'setup_completed_at',
        'phone',
        'block_street',
        'municipality',
        'barangay',
        'postal_code',
        'latitude',
        'longitude',
        'cover_photo',
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
        'phone' => PhilippinePhoneCast::class,
        'offers_pickup' => 'boolean',
        'offers_delivery' => 'boolean',
        'delivery_fixed_fee' => 'decimal:2',
        'delivery_base_fee' => 'decimal:2',
        'delivery_per_km_rate' => 'decimal:2',
        'delivery_free_km' => 'decimal:2',
        'delivery_min_fee' => 'decimal:2',
        'delivery_max_km' => 'decimal:2',
        'delivery_pricing_set_at' => 'datetime',
        'platform_rating_prompted_at' => 'datetime',
        'setup_completed_at' => 'datetime',
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

    public function hasCoordinates(): bool
    {
        return $this->latitude !== null && $this->longitude !== null;
    }

    public function hasDeliveryPricing(): bool
    {
        return $this->delivery_pricing_set_at !== null;
    }

    /** The shop chose to deliver but has not set what delivery costs yet. */
    public function needsDeliveryPricing(): bool
    {
        return (bool) $this->offers_delivery && ! $this->hasDeliveryPricing();
    }

    /** Delivery is available only once the pricing is set, so no run is ever given away free. */
    public function canDeliver(): bool
    {
        return (bool) $this->offers_delivery && $this->hasDeliveryPricing();
    }

    public function hasPaymongo(): bool
    {
        return ! empty($this->paymongo_secret_key) && ! empty($this->paymongo_public_key);
    }

    public function latestOrder(): HasOne
    {
        return $this->hasOne(Order::class, 'user_id', 'owner_id')
            ->activeSubscription()
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

    public function agreementAcceptances(): HasMany
    {
        return $this->hasMany(BusinessAgreementAcceptance::class);
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
