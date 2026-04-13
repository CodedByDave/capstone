<?php

namespace App\Models;

use App\Models\ShopService;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class ShopOrder extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'shop_id',
        'user_id',
        'order_source',
        'branch_name',
        'service_id',
        'order_number',
        'customer_name',
        'customer_phone',
        'customer_address',
        'special_instructions',
        'estimated_weight_kg',
        'actual_weight_kg',
        'pickup_type',
        'delivery_address',
        'pricing_model',
        'price_per_kg',
        'bundle_weight_kg',
        'bundle_price',
        'bundle_quantity',
        'additional_charges',
        'promotion_id',
        'discount_amount',
        'total_amount',
        'payment_method',
        'payment_status',
        'amount_paid',
        'paid_at',
        'status',
        'estimated_completion_at',
        'completed_at',
    ];

    protected $casts = [
        'estimated_weight_kg'     => 'decimal:2',
        'actual_weight_kg'        => 'decimal:2',
        'price_per_kg'            => 'decimal:2',
        'bundle_weight_kg'        => 'decimal:2',
        'bundle_price'            => 'decimal:2',
        'bundle_quantity'         => 'integer',
        'additional_charges'      => 'decimal:2',
        'discount_amount'         => 'decimal:2',
        'total_amount'            => 'decimal:2',
        'amount_paid'             => 'decimal:2',
        'paid_at'                 => 'datetime',
        'estimated_completion_at' => 'datetime',
        'completed_at'            => 'datetime',
    ];

    // ─── Enums ────────────────────────────────────────────────────────────────

    const PICKUP_TYPES = ['walk_in', 'pickup'];

    const PAYMENT_METHODS = ['cash', 'gcash', 'maya'];

    const PAYMENT_STATUSES = ['unpaid', 'partial', 'paid'];

    const STATUSES = ['pending', 'in_progress', 'completed'];

    // ─── Relationships ────────────────────────────────────────────────────────

    public function shop(): BelongsTo
    {
        return $this->belongsTo(Shop::class);
    }

    public function delivery(): HasOne
    {
        return $this->hasOne(Delivery::class, 'shop_order_id');
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function service(): BelongsTo
    {
        return $this->belongsTo(ShopService::class, 'service_id');
    }

    public function promotion(): BelongsTo
    {
        return $this->belongsTo(Promotion::class);
    }

    public function supplies(): BelongsToMany
    {
        return $this->belongsToMany(
            Inventory::class,
            'shop_order_supplies',
            'order_id',
            'inventory_id'
        )
            ->withPivot(['quantity_used', 'unit', 'inventory_movement_id'])
            ->withTimestamps();
    }

    // ─── Computed ─────────────────────────────────────────────────────────────

    /**
     * Recalculate and set total_amount based on current values.
     * Call before saving when actual_weight_kg or charges change.
     */
    public function computeTotal(): static
    {
        $this->total_amount =
            ($this->actual_weight_kg * $this->price_per_kg)
            + $this->additional_charges
            - $this->discount_amount;

        return $this;
    }

    // ─── Scopes ───────────────────────────────────────────────────────────────

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeInProgress($query)
    {
        return $query->where('status', 'in_progress');
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    public function scopeUnpaid($query)
    {
        return $query->where('payment_status', 'unpaid');
    }

    public function scopeForShop($query, int $shopId)
    {
        return $query->where('shop_id', $shopId);
    }

    // ─── Helpers ──────────────────────────────────────────────────────────────

    public function isPaid(): bool
    {
        return $this->payment_status === 'paid';
    }

    public function isCompleted(): bool
    {
        return $this->status === 'completed';
    }

    public function markAsPaid(): void
    {
        $this->update([
            'payment_status' => 'paid',
            'amount_paid'    => $this->total_amount,
            'paid_at'        => now(),
        ]);
    }

    public function markAsCompleted(): void
    {
        $this->update([
            'status'       => 'completed',
            'completed_at' => now(),
        ]);
    }
}
