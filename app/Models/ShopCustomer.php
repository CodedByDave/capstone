<?php

namespace App\Models;

use App\Casts\PhilippinePhoneCast;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

/** A customer of one shop: one record however many orders they place. */
class ShopCustomer extends Model
{
    protected static function booted(): void
    {
        static::creating(function (ShopCustomer $customer) {
            $customer->public_id ??= (string) Str::ulid();
        });
    }

    protected $fillable = [
        'public_id',
        'shop_id',
        'user_id',
        'name',
        'phone',
        'email',
        'identity_key',
    ];

    protected $casts = [
        'phone' => PhilippinePhoneCast::class,
    ];

    public function shop(): BelongsTo
    {
        return $this->belongsTo(Shop::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function orders(): HasMany
    {
        return $this->hasMany(ShopOrder::class, 'shop_customer_id');
    }

    public function scopeForShop($query, int $shopId)
    {
        return $query->where('shop_id', $shopId);
    }
}
