<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Inventory extends Model
{
    use SoftDeletes;
    protected $table = 'inventory';

    protected $fillable = [
        'shop_id',
        'inventory_categories_id',
        'supplier_id',
        'name',
        'sku',
        'description',
        'unit',
        'quantity',
        'min_stock',
        'max_stock',
        'unit_price',
        'selling_price',
        'status',
    ];

    protected $casts = [
        'unit_price'    => 'decimal:2',
        'selling_price' => 'decimal:2',
        'quantity'      => 'integer',
        'min_stock'     => 'integer',
        'max_stock'     => 'integer',
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

    public function getIsLowStockAttribute(): bool
    {
        return $this->quantity <= $this->min_stock;
    }

    public function getStockStatusAttribute(): string
    {
        if ($this->quantity === 0) return 'out_of_stock';
        if ($this->quantity <= $this->min_stock) return 'low';
        if ($this->max_stock && $this->quantity >= $this->max_stock) return 'overstock';
        return 'normal';
    }

    // Scopes

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
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
        $after  = $before + $quantity;

        $this->update(['quantity' => $after]);

        $movement = $this->movements()->create([
            'shop_id'          => $this->shop_id,
            'user_id'          => $userId,
            'type'             => $type,
            'quantity'         => $quantity,
            'quantity_before'  => $before,
            'quantity_after'   => $after,
            'reference_number' => $referenceNumber,
            'notes'            => $notes,
        ]);

        // Trigger low stock alert if threshold crossed
        if ($after <= $this->min_stock) {
            LowStockAlert::firstOrCreate(
                [
                    'inventory_id' => $this->id,
                    'status'       => 'unread',
                ],
                [
                    'shop_id'            => $this->shop_id,
                    'quantity_at_alert'  => $after,
                    'min_stock_at_alert' => $this->min_stock,
                ]
            );
        }

        return $movement;
    }
}
