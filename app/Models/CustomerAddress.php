<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

/**
 * An address a customer saved so they can pick it when placing an order.
 * Orders copy the address text and pin, so later edits never change past orders.
 */
class CustomerAddress extends Model
{
    use HasFactory;

    public const LABELS = ['home', 'work', 'other'];

    protected $fillable = [
        'user_id',
        'label',
        'recipient_name',
        'phone',
        'address',
        'latitude',
        'longitude',
        'is_default',
    ];

    protected $hidden = ['id', 'user_id'];

    protected function casts(): array
    {
        return [
            'latitude' => 'float',
            'longitude' => 'float',
            'is_default' => 'boolean',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (CustomerAddress $address) {
            $address->public_id ??= (string) Str::ulid();
        });
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
