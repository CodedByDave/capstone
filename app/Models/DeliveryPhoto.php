<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class DeliveryPhoto extends Model
{
    public const KIND_PROOF = 'proof';

    /** The cash a rider collected at the door. */
    public const KIND_CASH = 'cash';

    /** The laundry as the rider collected it from the customer. */
    public const KIND_PICKUP = 'pickup';

    /** The laundry as the rider handed it over to the customer. */
    public const KIND_DELIVERY = 'delivery';

    protected $fillable = ['delivery_id', 'uploaded_by', 'path', 'kind'];

    /** The shop board only needs the public id; the stored path and row ids stay server-side. */
    protected $hidden = ['id', 'delivery_id', 'uploaded_by', 'path', 'updated_at'];

    protected static function booted(): void
    {
        static::creating(function (self $photo) {
            $photo->public_id ??= (string) Str::ulid();
        });
    }

    public function getRouteKeyName(): string
    {
        return 'public_id';
    }

    public function delivery(): BelongsTo
    {
        return $this->belongsTo(Delivery::class);
    }

    /** The run even when the shop archived it, so the shop can still open its proof. */
    public function deliveryIncludingArchived(): BelongsTo
    {
        return $this->belongsTo(Delivery::class, 'delivery_id')->withTrashed();
    }

    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }
}
