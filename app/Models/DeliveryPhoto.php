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

    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }
}
