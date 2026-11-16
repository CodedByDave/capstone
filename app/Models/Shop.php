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

    public const OWNERSHIP_INDEPENDENT = 'independent';

    public const OWNERSHIP_COMPANY = 'company_owned';

    public const OWNERSHIP_FRANCHISEE = 'franchisee';

    public const DRIVER_PAY_NONE = 'none';

    public const DRIVER_PAY_DELIVERY_FEE = 'delivery_fee';

    public const DRIVER_PAY_DAILY_RATE = 'daily_rate';

    public const DRIVER_PAY_MODES = [self::DRIVER_PAY_NONE, self::DRIVER_PAY_DELIVERY_FEE, self::DRIVER_PAY_DAILY_RATE];

    public const DELIVERY_FEE_FIXED = 'fixed';

    public const DELIVERY_FEE_PER_KM = 'per_km';

    public const DELIVERY_FEE_MODES = [self::DELIVERY_FEE_FIXED, self::DELIVERY_FEE_PER_KM];

    public const DEFAULT_RIDER_VEHICLE_TYPE = 'Motorcycle';

    protected static function booted(): void
    {
        static::creating(function (Shop $shop) {
            $shop->public_id ??= (string) Str::ulid();
        });
    }

    /**
     * Secrets never leave the server, even when a page loads a whole shop (an order's details
     * page loads its shop). They are still readable in PHP; only serialization skips them.
     */
    protected $hidden = ['paymongo_secret_key'];

    protected $fillable = [
        'public_id',
        'owner_id',
        'shop_name',
        'ownership_type',
        'franchise_brand_name',
        'franchisor_name',
        'offers_pickup',
        'offers_delivery',
        'has_suppliers',
        'delivery_fee_mode',
        'delivery_fixed_fee',
        'delivery_base_fee',
        'delivery_per_km_rate',
        'delivery_free_km',
        'delivery_min_fee',
        'delivery_max_km',
        'delivery_pricing_set_at',
        'driver_pay_mode',
        'rush_fee',
        'rider_min_weight_kg',
        'rider_max_weight_kg',
        'rider_vehicle_type',
        'rider_vehicle_types',
        'rider_vehicle_limits',
        'is_open_24_7',
        'opens_at',
        'closes_at',
        'operating_days',
        'daily_hours',
        'driver_daily_rate',
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
        'has_suppliers' => 'boolean',
        'is_open_24_7' => 'boolean',
        'operating_days' => 'array',
        'daily_hours' => 'array',
        'delivery_fixed_fee' => 'decimal:2',
        'delivery_base_fee' => 'decimal:2',
        'delivery_per_km_rate' => 'decimal:2',
        'delivery_free_km' => 'decimal:2',
        'delivery_min_fee' => 'decimal:2',
        'delivery_max_km' => 'decimal:2',
        'delivery_pricing_set_at' => 'datetime',
        'driver_daily_rate' => 'decimal:2',
        'rush_fee' => 'decimal:2',
        'rider_min_weight_kg' => 'decimal:2',
        'rider_max_weight_kg' => 'decimal:2',
        'rider_vehicle_types' => 'array',
        'rider_vehicle_limits' => 'array',
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

    /** Weekdays the shop opens on (0 = Sunday ... 6 = Saturday); every day when none were chosen. */
    public function openDays(): array
    {
        $days = collect($this->operating_days ?? [])
            ->map(fn ($day) => (int) $day)
            ->filter(fn (int $day) => $day >= 0 && $day <= 6)
            ->unique()
            ->sort()
            ->values()
            ->all();

        return $days === [] ? [0, 1, 2, 3, 4, 5, 6] : $days;
    }

    /**
     * Whether the shop is open at a moment: it must open on that weekday and, unless it is open
     * 24 hours, the time must fall between opening and closing. Hours that pass midnight belong to
     * the day the shop opened, so 10pm-6am on Friday still covers Saturday 2am.
     */
    public function isOpenAt(?\DateTimeInterface $moment = null): bool
    {
        $moment = $moment ? \Illuminate\Support\Carbon::instance($moment) : now();
        $days = $this->openDays();

        if ($this->is_open_24_7) {
            return in_array($moment->dayOfWeek, $days, true);
        }

        $time = $moment->format('H:i');
        $today = in_array($moment->dayOfWeek, $days, true) ? $this->hoursForDay($moment->dayOfWeek) : null;

        if ($today === null && in_array($moment->dayOfWeek, $days, true)) {
            return true; // Open that day with no hours set: open all day.
        }

        if ($today !== null) {
            [$opens, $closes] = $today;
            if ($opens < $closes ? ($time >= $opens && $time < $closes) : $time >= $opens) {
                return true;
            }
        }

        // Overnight hours from yesterday's opening run into the early hours of today.
        $yesterday = $moment->copy()->subDay()->dayOfWeek;
        $previous = in_array($yesterday, $days, true) ? $this->hoursForDay($yesterday) : null;

        return $previous !== null && $previous[0] >= $previous[1] && $time < $previous[1];
    }

    /**
     * Opening and closing time ("H:i") for a weekday: that day's own hours, else the shop-wide hours.
     * Null when no hours are set at all (open all day).
     *
     * @return array{0: string, 1: string}|null
     */
    public function hoursForDay(int $day): ?array
    {
        $own = $this->daily_hours[(string) $day] ?? null;

        if (is_array($own) && ! empty($own['opens_at']) && ! empty($own['closes_at'])) {
            return [substr($own['opens_at'], 0, 5), substr($own['closes_at'], 0, 5)];
        }

        if ($this->opens_at && $this->closes_at) {
            return [substr($this->opens_at, 0, 5), substr($this->closes_at, 0, 5)];
        }

        return null;
    }

    /**
     * Hours for every open weekday, keyed by weekday number, for the UI.
     *
     * @return array<int, array{opens_at: string, closes_at: string}>
     */
    public function dailyHoursMap(): array
    {
        $map = [];

        foreach ($this->openDays() as $day) {
            $hours = $this->hoursForDay($day) ?? ['08:00', '17:00'];
            $map[$day] = ['opens_at' => $hours[0], 'closes_at' => $hours[1]];
        }

        return $map;
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
}
