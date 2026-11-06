<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;

class Inventory extends Model
{
    use SoftDeletes;

    protected $table = 'inventory';

    protected $fillable = [
        'shop_id',
        'inventory_categories_id',
        'supplier_id',
        'name',
        'description',
        'photo_path',
        'unit',
        'package_unit',
        'package_size',
        'quantity',
        'min_stock',
        'max_stock',
        'unit_price',
        'selling_price',
        'addon_enabled',
        'addon_name',
        'addon_quantity',
        'addon_price',
        'retail_enabled',
        'retail_name',
        'retail_pack_size',
        'retail_price',
        'notes',
        'status',
    ];

    protected $appends = ['photo_url'];

    protected $casts = [
        'unit_price' => 'decimal:8',
        'selling_price' => 'decimal:4',
        'addon_enabled' => 'boolean',
        'addon_quantity' => 'integer',
        'addon_price' => 'decimal:2',
        'retail_enabled' => 'boolean',
        'retail_pack_size' => 'integer',
        'retail_price' => 'decimal:2',
        'quantity' => 'integer',
        'package_size' => 'integer',
        'min_stock' => 'integer',
        'max_stock' => 'integer',
    ];

    // Relationships

    public function shop(): BelongsTo
    {
        return $this->belongsTo(Shop::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(InventoryCategory::class, 'inventory_categories_id');
    }

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    public function movements(): HasMany
    {
        return $this->hasMany(InventoryMovement::class);
    }

    public function lowStockAlerts(): HasMany
    {
        return $this->hasMany(LowStockAlert::class);
    }

    // ── Accessors ──────────────────────────────────────────

    /** Public URL of the item photo, or null when it has none (or the column was not selected). */
    protected function photoUrl(): Attribute
    {
        return Attribute::get(function () {
            $path = $this->attributes['photo_path'] ?? null;

            return $path ? Storage::disk('public')->url($path) : null;
        });
    }

    public function getIsLowStockAttribute(): bool
    {
        return $this->quantity <= $this->min_stock;
    }

    public function getStockStatusAttribute(): string
    {
        if ($this->quantity === 0) {
            return 'out_of_stock';
        }
        if ($this->quantity <= $this->min_stock) {
            return 'low';
        }
        if ($this->max_stock && $this->quantity >= $this->max_stock) {
            return 'overstock';
        }

        return 'normal';
    }

    // Scopes

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    /** Items enabled for an additional charge, plus any ids that must stay listed (e.g. already on the record). */
    public function scopeUsableAsSupply($query, array $keepIds = [])
    {
        return $query->where(function ($q) use ($keepIds) {
            $q->where('addon_enabled', true)->orWhereIn('id', $keepIds);
        });
    }

    public function scopeForShop($query, int $shopId)
    {
        return $query->where('shop_id', $shopId);
    }

    public function scopeLowStock($query)
    {
        return $query->whereColumn('quantity', '<=', 'min_stock');
    }

    public function scopeOutOfStock($query)
    {
        return $query->where('quantity', 0);
    }

    // Stock Mutation Helper

    public function adjustStock(
        string $type,
        int $quantity,
        ?int $userId = null,
        ?string $referenceNumber = null,
        ?string $notes = null
    ): InventoryMovement {
        $before = $this->quantity;
        $after = $before + $quantity;

        $this->update(['quantity' => $after]);

        $movement = $this->movements()->create([
            'shop_id' => $this->shop_id,
            'user_id' => $userId,
            'type' => $type,
            'quantity' => $quantity,
            'quantity_before' => $before,
            'quantity_after' => $after,
            'reference_number' => $referenceNumber,
            'notes' => $notes,
        ]);

        // Trigger low stock alert if threshold crossed
        if ($after <= $this->min_stock) {
            LowStockAlert::firstOrCreate(
                [
                    'inventory_id' => $this->id,
                    'status' => 'unread',
                ],
                [
                    'shop_id' => $this->shop_id,
                    'quantity_at_alert' => $after,
                    'min_stock_at_alert' => $this->min_stock,
                ]
            );
        }

        return $movement;
    }
}
