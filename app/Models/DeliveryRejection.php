<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** A driver turning down a run they were assigned, with the reason and a photo as proof. */
class DeliveryRejection extends Model
{
    public const REASONS = [
        'emergency' => 'Emergency',
        'vehicle_problem' => 'Vehicle problem or breakdown',
        'feeling_unwell' => 'Feeling unwell',
        'bad_weather' => 'Unsafe weather or road conditions',
        'other' => 'Other reason',
    ];

    protected $fillable = ['delivery_id', 'rider_id', 'reason', 'note', 'photo_path'];

    protected $hidden = ['photo_path'];

    protected static function booted(): void
    {
        static::creating(function (self $rejection) {
            $rejection->public_id ??= (string) \Illuminate\Support\Str::ulid();
        });
    }

    public function getRouteKeyName(): string
    {
        return 'public_id';
    }

    public function reasonLabel(): string
    {
        return self::REASONS[$this->reason] ?? ucfirst(str_replace('_', ' ', $this->reason));
    }

    public function delivery(): BelongsTo
    {
        return $this->belongsTo(Delivery::class);
    }

    /** The run even when the shop archived it, so the shop can still open the rejection proof. */
    public function deliveryIncludingArchived(): BelongsTo
    {
        return $this->belongsTo(Delivery::class, 'delivery_id')->withTrashed();
    }

    public function rider(): BelongsTo
    {
        return $this->belongsTo(Rider::class);
    }
}
