<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ShopServicePricing extends Model
{
    use SoftDeletes;

    protected $table = 'services_and_pricing';

    protected $fillable = [
        'shop_id',
        'service_name',
        'description',
        'is_active',
        'pricing_model',
        'price_per_kg',
        'bundle_weight_kg',
        'bundle_price',
        'estimated_hours',
    ];

    protected $casts = [
        'is_active'        => 'boolean',
        'price_per_kg'     => 'decimal:2',
        'bundle_weight_kg' => 'decimal:2',
        'bundle_price'     => 'decimal:2',
    ];

    const PRICING_MODELS = ['per_kg', 'per_bundle'];

    // ─── Relationships ─────────────────────────────────

    public function shop(): BelongsTo
    {
        return $this->belongsTo(Shop::class);
    }

    public function orders(): HasMany
    {
        return $this->hasMany(ShopOrder::class, 'service_id');
    }

    // ─── Helpers ───────────────────────────────────────

    public function isPerKg(): bool
    {
        return $this->pricing_model === 'per_kg';
    }

    public function isPerBundle(): bool
    {
        return $this->pricing_model === 'per_bundle';
    }

    public function getFormattedPriceAttribute(): string
    {
        return $this->pricing_model === 'per_kg'
            ? "₱{$this->price_per_kg}/kg"
            : "₱{$this->bundle_price} / {$this->bundle_weight_kg}kg bundle";
    }
}
