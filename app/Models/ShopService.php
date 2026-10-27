<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;

class ShopService extends Model
{
    use SoftDeletes;

    protected $table = 'services_and_pricing';

    protected $fillable = [
        'shop_id',
        'service_name',
        'category',
        'description',
        'photo_path',
        'is_active',
        'pricing_model',
        'price_per_kg',
        'bundle_weight_kg',
        'bundle_price',
        'price_per_piece',
        'estimated_hours',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'price_per_kg' => 'decimal:2',
        'bundle_weight_kg' => 'decimal:2',
        'bundle_price' => 'decimal:2',
        'price_per_piece' => 'decimal:2',
    ];

    protected $appends = ['photo_url'];

    const PRICING_MODELS = ['per_kg', 'per_bundle', 'per_piece'];

    const CATEGORIES = [
        'self_service',
        'full_service',
        'dry_cleaning',
        'ironing_pressing',
        'pickup_delivery',
        'specialty_bulk',
    ];

    // ─── Relationships ─────────────────────────────────

    public function shop(): BelongsTo
    {
        return $this->belongsTo(Shop::class);
    }

    public function orders(): HasMany
    {
        return $this->hasMany(ShopOrder::class, 'service_id');
    }

    /** Inventory items this service uses: `quantity` per kg, or per bundle for bundle services. */
    public function supplies(): BelongsToMany
    {
        return $this->belongsToMany(Inventory::class, 'service_supplies', 'service_id', 'inventory_id')
            ->withPivot(['quantity', 'unit'])
            ->withTimestamps();
    }

    // ─── Accessors ─────────────────────────────────────

    /** Public URL of the service photo, or null when the service has none (or the column was not selected). */
    protected function photoUrl(): Attribute
    {
        return Attribute::get(function () {
            $path = $this->attributes['photo_path'] ?? null;

            return $path ? Storage::disk('public')->url($path) : null;
        });
    }

    // ─── Helpers ───────────────────────────────────────

    public function isPerKg(): bool
    {
        return $this->pricing_model === 'per_kg';
    }

    public function isPerPiece(): bool
    {
        return $this->pricing_model === 'per_piece';
    }

    public function isPerBundle(): bool
    {
        return $this->pricing_model === 'per_bundle';
    }

    public function getFormattedPriceAttribute(): string
    {
        return match ($this->pricing_model) {
            'per_kg' => "₱{$this->price_per_kg}/kg",
            'per_piece' => "₱{$this->price_per_piece}/piece",
            default => "₱{$this->bundle_price} / {$this->bundle_weight_kg}kg bundle",
        };
    }
}
