<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class Delivery extends Model
{
    protected $fillable = [
        'shop_id', 'shop_order_id', 'rider_id',
        'customer_name', 'customer_phone', 'customer_email', 'delivery_address',
        'status', 'notes', 'driver_token',
        'assigned_at', 'picked_up_at', 'delivered_at',
    ];

    protected static function booted(): void
    {
        static::creating(function (self $delivery) {
            $delivery->driver_token = Str::random(48);
        });
    }

    protected $casts = [
        'assigned_at'  => 'datetime',
        'picked_up_at' => 'datetime',
        'delivered_at' => 'datetime',
    ];

    public const STATUSES = ['pending', 'assigned', 'picked_up', 'delivered', 'failed'];

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
