<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

/** A supply a supplier has in stock, with the price they sell it at. */
class SupplierItem extends Model
{
    protected static function booted(): void
    {
        static::creating(function (SupplierItem $item) {
            $item->public_id ??= (string) Str::ulid();
        });
    }

    protected $fillable = [
        'public_id',
        'supplier_id',
        'name',
        'unit',
        'stock_quantity',
        'unit_price',
    ];

    protected $casts = [
        'stock_quantity' => 'decimal:2',
        'unit_price' => 'decimal:2',
    ];

    protected $hidden = ['id', 'supplier_id'];

    public function getRouteKeyName(): string
    {
        return 'public_id';
    }

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }
}
