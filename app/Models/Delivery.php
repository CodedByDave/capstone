<?php

namespace App\Models;

use App\Casts\PhilippinePhoneCast;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class Delivery extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'shop_id', 'shop_order_id', 'type', 'rider_id',
        'customer_name', 'customer_phone', 'customer_email', 'delivery_address',
        'destination_lat', 'destination_lng', 'distance_km', 'delivery_fee',
        'rider_lat', 'rider_lng', 'rider_located_at',
        'courier', 'courier_reference', 'courier_tracking_url', 'courier_driver_name',
        'courier_driver_phone', 'courier_vehicle_plate', 'courier_cost',
        'status', 'notes', 'driver_token',
        'assigned_at', 'accepted_at', 'picked_up_at', 'delivered_at',
    ];

    public function getRouteKeyName(): string
    {
        return 'public_id';
    }

    protected static function booted(): void
    {
        static::creating(function (self $delivery) {
            $delivery->public_id ??= (string) Str::ulid();
            $delivery->driver_token = Str::random(48);
        });

        // Archiving keeps the proof photos. Only a permanent delete cascades the photo rows,
        // and then their files must go too.
        static::forceDeleting(function (self $delivery) {
            $delivery->photos->each(fn (DeliveryPhoto $photo) => Storage::disk('private')->delete($photo->path));
        });
    }

    protected $casts = [
        'customer_phone' => PhilippinePhoneCast::class,
        'assigned_at' => 'datetime',
        'accepted_at' => 'datetime',
        'picked_up_at' => 'datetime',
        'delivered_at' => 'datetime',
        'destination_lat' => 'float',
        'destination_lng' => 'float',
        'distance_km' => 'decimal:2',
        'delivery_fee' => 'decimal:2',
        'courier_cost' => 'decimal:2',
        'courier_driver_phone' => PhilippinePhoneCast::class,
        'rider_lat' => 'float',
        'rider_lng' => 'float',
        'rider_located_at' => 'datetime',
    ];

    public const STATUSES = ['pending', 'assigned', 'picked_up', 'delivered', 'failed'];

    /** Statuses during which a rider is on the road and may share a live location. */
    public const TRACKABLE_STATUSES = ['assigned', 'picked_up'];

    /** A pickup run collects the laundry from the customer; a delivery brings it back. */
    public const TYPE_PICKUP = 'pickup';

    public const TYPE_DELIVERY = 'delivery';

    public const COURIER_OWN_RIDER = 'own_rider';

    public const COURIER_LALAMOVE = 'lalamove';

    public const COURIERS = [self::COURIER_OWN_RIDER, self::COURIER_LALAMOVE];

    public function isTrackable(): bool
    {
        return in_array($this->status, self::TRACKABLE_STATUSES, true);
    }

    public function isPickupRun(): bool
    {
        return $this->type === self::TYPE_PICKUP;
    }

    public function usesLalamove(): bool
    {
        return $this->courier === self::COURIER_LALAMOVE;
    }

    public function photos(): HasMany
    {
        return $this->hasMany(DeliveryPhoto::class)->oldest();
    }

    public function rejections(): HasMany
    {
        return $this->hasMany(DeliveryRejection::class);
    }

    /** The assigned rider has not yet said yes or no to this run. */
    public function awaitsAcceptance(): bool
    {
        return $this->status === 'assigned'
            && $this->rider_id !== null
            && $this->accepted_at === null
            && ! $this->usesLalamove();
    }

    /**
     * What the customer should do while no rider can take this run; null when one has it. A pickup
     * means bringing the laundry in, and a delivery means collecting it, since it is already at the shop.
     */
    public function noRiderNotice(?string $shopName): ?string
    {
        if ($this->status !== 'pending' || $this->rider_id !== null || $this->usesLalamove()) {
            return null;
        }

        $shop = $shopName ?: 'the laundry shop';

        return $this->isPickupRun()
            ? "No rider is available right now. Please drop off your laundry at {$shop}."
            : "No rider is available right now. Please collect your laundry at {$shop}.";
    }

    public function shop(): BelongsTo
    {
        return $this->belongsTo(Shop::class);
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(ShopOrder::class, 'shop_order_id');
    }

    public function rider(): BelongsTo
    {
        return $this->belongsTo(Rider::class);
    }
}
