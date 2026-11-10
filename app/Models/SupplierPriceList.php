<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

/** A photo of a supplier's printed price list and what OCR found when it was checked against their items. */
class SupplierPriceList extends Model
{
    public const STATUS_PENDING = 'pending';

    public const STATUS_VERIFIED = 'verified';

    public const STATUS_MISMATCH = 'mismatch';

    public const STATUS_UNREADABLE = 'unreadable';

    protected static function booted(): void
    {
        static::creating(function (SupplierPriceList $priceList) {
            $priceList->public_id ??= (string) Str::ulid();
        });
    }

    protected $fillable = [
        'public_id',
        'supplier_id',
        'photo_path',
        'ocr_text',
        'status',
        'match_results',
        'checked_at',
    ];

    protected $casts = [
        'match_results' => 'array',
        'checked_at' => 'datetime',
    ];

    protected $hidden = ['id', 'supplier_id', 'photo_path', 'ocr_text'];

    public function getRouteKeyName(): string
    {
        return 'public_id';
    }

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }
}
