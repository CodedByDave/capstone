<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

/**
 * A shop owner's star rating of the LaundryHub platform itself.
 */
class PlatformRating extends Model
{
    protected $fillable = ['shop_id', 'user_id', 'rating', 'comment'];

    protected $casts = ['rating' => 'integer'];

    protected static function booted(): void
    {
        static::creating(function (self $rating) {
            $rating->public_id ??= (string) Str::ulid();
        });
    }

    public function getRouteKeyName(): string
    {
        return 'public_id';
    }

    public function shop(): BelongsTo
    {
        return $this->belongsTo(Shop::class);
    }

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
