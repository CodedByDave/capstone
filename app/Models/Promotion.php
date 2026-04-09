<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Carbon\Carbon;

class Promotion extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'shop_id',
        'name',
        'description',
        'type',
        'value',
        'min_order_amount',
        'is_active',
        'starts_at',
        'ends_at',
    ];

    protected $casts = [
        'value'            => 'decimal:2',
        'min_order_amount' => 'decimal:2',
        'is_active'        => 'boolean',
        'starts_at'        => 'date:Y-m-d',
        'ends_at'          => 'date:Y-m-d',
    ];

    public function shop(): BelongsTo
    {
        return $this->belongsTo(Shop::class);
    }

    public function orders(): HasMany
    {
        return $this->hasMany(ShopOrder::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeValid($query)
    {
        $today = Carbon::today()->toDateString();
        return $query->where(function ($q) use ($today) {
            $q->whereNull('starts_at')->orWhere('starts_at', '<=', $today);
        })->where(function ($q) use ($today) {
            $q->whereNull('ends_at')->orWhere('ends_at', '>=', $today);
        });
    }

    /**
     * Compute the discount amount for a given order total.
     */
    public function computeDiscount(float $orderTotal): float
    {
        if ($this->min_order_amount && $orderTotal < (float) $this->min_order_amount) {
            return 0.0;
        }

        if ($this->type === 'percentage') {
            return round($orderTotal * ((float) $this->value / 100), 2);
        }

        return min((float) $this->value, $orderTotal);
    }

    public function isValid(): bool
    {
        if (!$this->is_active) return false;

        $today = Carbon::today();
        if ($this->starts_at && $today->lt($this->starts_at)) return false;
        if ($this->ends_at && $today->gt($this->ends_at)) return false;

        return true;
    }
}
