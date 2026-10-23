<?php

namespace App\Models;

use App\Casts\PhilippinePhoneCast;
use App\Services\ShopContext;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class ShopOrder extends Model
{
    use SoftDeletes;

    protected static function booted(): void
    {
        static::creating(function (ShopOrder $order): void {
            $order->payment_public_id ??= (string) Str::ulid();
        });
    }

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
        'deliver_to_customer',
        'delivery_address',
        'pricing_model',
        'price_per_kg',
        'bundle_weight_kg',
        'bundle_price',
        'bundle_quantity',
        'additional_charges',
        'promotion_id',
        'discount_amount',
        'delivery_fee',
        'total_amount',
        'payment_method',
        'payment_status',
        'amount_paid',
        'paid_at',
        'paymongo_session_id',
        'paymongo_payment_id',
        'status',
        'estimated_completion_at',
        'in_progress_at',
        'overdue_notified_at',
        'completed_at',
    ];

    protected $casts = [
        'customer_phone' => PhilippinePhoneCast::class,
        'deliver_to_customer' => 'boolean',
        'estimated_weight_kg' => 'decimal:2',
        'actual_weight_kg' => 'decimal:2',
        'price_per_kg' => 'decimal:2',
        'bundle_weight_kg' => 'decimal:2',
        'bundle_price' => 'decimal:2',
        'bundle_quantity' => 'integer',
        'additional_charges' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'delivery_fee' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'amount_paid' => 'decimal:2',
        'paid_at' => 'datetime',
        'estimated_completion_at' => 'datetime',
        'in_progress_at' => 'datetime',
        'overdue_notified_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    // ─── Enums ────────────────────────────────────────────────────────────────

    const PICKUP_TYPES = ['walk_in', 'pickup'];

    const PAYMENT_METHODS = ['cash', 'gcash', 'maya', 'card', 'grabpay', 'online'];

    const PAYMENT_STATUSES = ['unpaid', 'partial', 'paid'];

    const STATUSES = ['pending', 'in_progress', 'completed', 'cancelled'];

    /**
     * Shop owners and staff only ever resolve orders of their own shop. An order of another shop
     * is "not found", exactly like one that does not exist.
     */
    public function resolveRouteBinding($value, $field = null): ?Model
    {
        $query = $this->resolveRouteBindingQuery($this, $value, $field);
        $user = auth()->user();

        if ($user && in_array($user->role, ['owner', 'staff'], true)) {
            $query->where('shop_id', app(ShopContext::class)->shopIdFor($user));
        }

        return $query->first();
    }

    // ─── Relationships ────────────────────────────────────────────────────────

    public function shop(): BelongsTo
    {
        return $this->belongsTo(Shop::class);
    }

    /** The delivery that brings the finished laundry back to the customer. */
    public function delivery(): HasOne
    {
        return $this->hasOne(Delivery::class, 'shop_order_id')->where('type', Delivery::TYPE_DELIVERY);
    }

    /** The run that collects the laundry from the customer's address. */
    public function pickupRun(): HasOne
    {
        return $this->hasOne(Delivery::class, 'shop_order_id')->where('type', Delivery::TYPE_PICKUP);
    }

    /** Every payment toward this order: received at the shop, or made online. */
    public function payments(): HasMany
    {
        return $this->hasMany(ShopOrderPayment::class);
    }

    /** The customer's star rating of this order, if they left one. */
    public function rating(): HasOne
    {
        return $this->hasOne(OrderRating::class);
    }

    /** Problems the customer reported about this order. */
    public function complaints(): HasMany
    {
        return $this->hasMany(OrderComplaint::class);
    }

    public function customerAgreementAcceptance(): HasOne
    {
        return $this->hasOne(CustomerAgreementAcceptance::class);
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
            + $this->delivery_fee
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

    /**
     * Amount still owed on the order, never below zero.
     */
    public function balanceDue(): float
    {
        return round(max(0, (float) $this->total_amount - (float) $this->amount_paid), 2);
    }

    public function isPaid(): bool
    {
        return $this->payment_status === 'paid';
    }

    public function isCompleted(): bool
    {
        return $this->status === 'completed';
    }

    public function markAsCompleted(): void
    {
        $this->update([
            'status' => 'completed',
            'completed_at' => now(),
        ]);
    }
}
