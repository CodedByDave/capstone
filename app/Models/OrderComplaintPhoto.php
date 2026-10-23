<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class OrderComplaintPhoto extends Model
{
    protected $fillable = ['order_complaint_id', 'path'];

    /** Only the public id leaves the server; the stored path and row ids stay internal. */
    protected $hidden = ['id', 'order_complaint_id', 'path', 'updated_at'];

    protected static function booted(): void
    {
        static::creating(function (self $photo): void {
            $photo->public_id ??= (string) Str::ulid();
        });
    }

    public function complaint(): BelongsTo
    {
        return $this->belongsTo(OrderComplaint::class, 'order_complaint_id');
    }
}
