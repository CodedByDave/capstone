<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

/**
 * A customer's report about a problem with one of their orders (a stain, a missing item, ...).
 */
class OrderComplaint extends Model
{
    public const TYPES = [
        'stain_damage' => 'Stain or damage',
        'missing_item' => 'Missing item',
        'wrong_item' => 'Wrong item returned',
        'wrong_charge' => 'Wrong or extra charge',
        'late' => 'Late or delayed',
        'service_quality' => 'Service quality',
        'other' => 'Other',
    ];

    public const STATUSES = ['open', 'under_review', 'resolved', 'rejected'];

    /** Statuses that still need the shop's attention. */
    public const ACTIVE_STATUSES = ['open', 'under_review'];

    /** Statuses a shop can move a complaint to. */
    public const RESPONSE_STATUSES = ['under_review', 'resolved', 'rejected'];

    protected $fillable = [
        'shop_order_id',
        'shop_id',
        'user_id',
        'type',
        'description',
        'status',
        'shop_reply',
        'replied_by',
        'replied_at',
        'resolved_at',
    ];

    protected $casts = [
        'replied_at' => 'datetime',
        'resolved_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::creating(function (OrderComplaint $complaint): void {
            $complaint->public_id ??= (string) Str::ulid();
        });
    }

    public function isActive(): bool
    {
        return in_array($this->status, self::ACTIVE_STATUSES, true);
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(ShopOrder::class, 'shop_order_id');
    }

    public function shop(): BelongsTo
    {
        return $this->belongsTo(Shop::class);
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function photos(): HasMany
    {
        return $this->hasMany(OrderComplaintPhoto::class);
    }
}
