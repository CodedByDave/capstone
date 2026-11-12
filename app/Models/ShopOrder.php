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
        'order_type',
        'service_id',
        'order_number',
        'customer_name',
        'customer_phone',
        'alternate_contact_name',
        'alternate_contact_phone',
        'customer_address',
        'customer_lat',
        'customer_lng',
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
        'price_per_piece',
        'piece_count',
        'bundle_quantity',
        'additional_charges',
        'promotion_id',
        'discount_amount',
        'delivery_fee',
        'pickup_fee',
        'tax_rate',
        'tax_amount',
        'total_amount',
        'payment_method',
        'payment_status',
        'amount_paid',
        'paid_at',
        'paymongo_session_id',
        'paymongo_payment_id',
        'status',
        'is_rush',
        'rush_fee',
        'rush_marked_at',
        'estimated_completion_at',
        'in_progress_at',
        'overdue_notified_at',
        'completed_at',
        'supplies_returned_at',
        'picked_up_at',
        'weight_proof_path',
        'weight_approval_status',
        'weight_proof_uploaded_at',
        'weight_decided_at',
    ];

    /** The storage path of the weighing photo is never sent to the browser. */
    protected $hidden = ['weight_proof_path'];

    protected $appends = ['has_weight_proof'];

    protected $casts = [
        'customer_phone' => PhilippinePhoneCast::class,
        'alternate_contact_phone' => PhilippinePhoneCast::class,
        'deliver_to_customer' => 'boolean',
        'customer_lat' => 'float',
        'customer_lng' => 'float',
        'estimated_weight_kg' => 'decimal:2',
        'actual_weight_kg' => 'decimal:2',
        'price_per_kg' => 'decimal:2',
        'bundle_weight_kg' => 'decimal:2',
        'bundle_price' => 'decimal:2',
        'price_per_piece' => 'decimal:2',
        'piece_count' => 'integer',
        'bundle_quantity' => 'integer',
        'additional_charges' => 'decimal:2',
        'is_rush' => 'boolean',
        'rush_fee' => 'decimal:2',
        'rush_marked_at' => 'datetime',
        'discount_amount' => 'decimal:2',
        'delivery_fee' => 'decimal:2',
        'pickup_fee' => 'decimal:2',
        'tax_rate' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'amount_paid' => 'decimal:2',
        'paid_at' => 'datetime',
        'estimated_completion_at' => 'datetime',
        'in_progress_at' => 'datetime',
        'overdue_notified_at' => 'datetime',
        'completed_at' => 'datetime',
        'supplies_returned_at' => 'datetime',
        'picked_up_at' => 'datetime',
        'weight_proof_uploaded_at' => 'datetime',
        'weight_decided_at' => 'datetime',
    ];

    // ─── Enums ────────────────────────────────────────────────────────────────

    const PICKUP_TYPES = ['walk_in', 'pickup'];

    /** 'laundry': a laundry service (with optional add-ons and products); 'products': retail products only. */
    const ORDER_TYPES = ['laundry', 'products'];

    const PAYMENT_METHODS = ['cash', 'gcash', 'maya', 'card', 'grabpay', 'online'];

    const PAYMENT_STATUSES = ['unpaid', 'partial', 'paid'];

    /** Orders still to be worked on. They form the shop's queue; an order leaves it once it is completed. */
    const QUEUE_STATUSES = ['pending', 'in_progress'];

    const STATUSES = ['pending', 'in_progress', 'completed', 'picked_up', 'cancelled'];

    /** The laundry is done: ready at the shop, or already collected by the customer. */
    const FINISHED_STATUSES = ['completed', 'picked_up'];

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

    /** Additional services billed on this order next to its main service. */
    public function serviceLines(): HasMany
    {
        return $this->hasMany(ShopOrderServiceLine::class, 'shop_order_id')->orderBy('id');
    }

    public function promotion(): BelongsTo
    {
        return $this->belongsTo(Promotion::class);
    }

    /** Supplies staff added by hand; the customer is charged for these. */
    public function supplies(): BelongsToMany
    {
        return $this->suppliesFrom('manual');
    }

    /** Supplies taken from stock because the order's service uses them; not charged to the customer. */
    public function serviceSupplies(): BelongsToMany
    {
        return $this->suppliesFrom('service');
    }

    private function suppliesFrom(string $source): BelongsToMany
    {
        return $this->belongsToMany(
            Inventory::class,
            'shop_order_supplies',
            'order_id',
            'inventory_id'
        )
            ->wherePivot('source', $source)
            ->withPivot(['quantity_used', 'unit', 'label', 'source', 'unit_price', 'unit_cost', 'inventory_movement_id'])
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
            + $this->pickup_fee
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
        return $query->whereIn('status', self::FINISHED_STATUSES);
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

    /**
     * Whether the amount to pay is final. A per-kg or bundle order is only final once the laundry is
     * weighed; a per-piece order is final the moment the items are counted into the order.
     */
    public function priceIsConfirmed(): bool
    {
        // Products are priced when the order is placed; nothing is weighed.
        if ($this->isProductOrder()) {
            return true;
        }

        // An online customer has to accept the shop's weight before the price is final.
        if ($this->awaitingWeightApproval()) {
            return false;
        }

        $mainPriced = $this->actual_weight_kg !== null || $this->pricing_model === 'per_piece';

        // An additional service that is still waiting for its weight leaves the total open too.
        return $mainPriced && ! $this->serviceLines->contains(
            fn (ShopOrderServiceLine $line) => $line->pricing_model !== 'per_piece' && $line->weight_kg === null,
        );
    }

    public function getHasWeightProofAttribute(): bool
    {
        return filled($this->weight_proof_path);
    }

    /** An online order whose shop-entered weight the customer has not approved yet. */
    /** A retail-products-only order: no laundry service, collected at the shop. */
    public function isProductOrder(): bool
    {
        return $this->order_type === 'products';
    }

    public function awaitingWeightApproval(): bool
    {
        return $this->order_source === 'online'
            && $this->weight_approval_status === 'pending';
    }

    public function isPaid(): bool
    {
        return $this->payment_status === 'paid';
    }

    public function isCompleted(): bool
    {
        return in_array($this->status, self::FINISHED_STATUSES, true);
    }

    public function markAsCompleted(): void
    {
        $this->update([
            'status' => 'completed',
            'completed_at' => now(),
        ]);
    }
}
