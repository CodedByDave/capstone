<?php

namespace App\Models;

use App\Casts\PhilippinePhoneCast;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class Delivery extends Model
{
    protected $fillable = [
        'shop_id', 'shop_order_id', 'rider_id',
        'customer_name', 'customer_phone', 'customer_email', 'delivery_address',
        'destination_lat', 'destination_lng', 'distance_km', 'delivery_fee',
        'rider_lat', 'rider_lng', 'rider_located_at',
        'status', 'notes', 'driver_token',
        'assigned_at', 'picked_up_at', 'delivered_at',
    ];

    protected static function booted(): void
    {
        static::creating(function (self $delivery) {
            $delivery->public_id ??= (string) Str::ulid();
            $delivery->driver_token = Str::random(48);
        });
    }

    protected $casts = [
        'customer_phone' => PhilippinePhoneCast::class,
        'assigned_at' => 'datetime',
        'picked_up_at' => 'datetime',
        'delivered_at' => 'datetime',
        'destination_lat' => 'float',
        'destination_lng' => 'float',
        'distance_km' => 'decimal:2',
        'delivery_fee' => 'decimal:2',
        'rider_lat' => 'float',
        'rider_lng' => 'float',
        'rider_located_at' => 'datetime',
    ];

    public const STATUSES = ['pending', 'assigned', 'picked_up', 'delivered', 'failed'];

    /** Statuses during which a rider is on the road and may share a live location. */
    public const TRACKABLE_STATUSES = ['assigned', 'picked_up'];

    public function isTrackable(): bool
    {
        return in_array($this->status, self::TRACKABLE_STATUSES, true);
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
