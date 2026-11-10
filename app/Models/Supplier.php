<?php

namespace App\Models;

use App\Casts\PhilippinePhoneCast;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Supplier extends Model
{
    use SoftDeletes;

    protected static function booted(): void
    {
        static::creating(function (Supplier $supplier) {
            $supplier->public_id ??= (string) Str::ulid();
        });
    }

    protected $fillable = [
        'public_id',
        'shop_id',
        'user_id',
        'name',
        'contact_person',
        'email',
        'phone',
        'address',
        'lead_time_days',
        'status',
        'notes',
    ];

    protected $casts = [
        'phone' => PhilippinePhoneCast::class,
    ];

    public function getRouteKeyName(): string
    {
        return 'public_id';
    }

    // Relationships

    public function shop(): BelongsTo
    {
        return $this->belongsTo(Shop::class);
    }

    /** The supplier's own login account, if one has been created. */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function inventory(): HasMany
    {
        return $this->hasMany(Inventory::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(SupplierItem::class);
    }

    public function priceLists(): HasMany
    {
        return $this->hasMany(SupplierPriceList::class);
    }

    public function latestPriceList(): HasOne
    {
        return $this->hasOne(SupplierPriceList::class)->latestOfMany();
    }

    // Scopes

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeForShop($query, int $shopId)
    {
        return $query->where('shop_id', $shopId);
    }
}
