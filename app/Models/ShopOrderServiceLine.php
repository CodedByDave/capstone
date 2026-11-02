<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** An additional service on an order, billed on top of the order's main service. */
class ShopOrderServiceLine extends Model
{
    protected $fillable = [
        'shop_order_id',
        'service_id',
        'service_name',
        'pricing_model',
        'price_per_kg',
        'bundle_weight_kg',
        'bundle_price',
        'price_per_piece',
        'weight_kg',
        'bundle_quantity',
        'piece_count',
        'amount',
    ];

    protected $casts = [
        'price_per_kg' => 'decimal:2',
        'bundle_weight_kg' => 'decimal:2',
        'bundle_price' => 'decimal:2',
        'price_per_piece' => 'decimal:2',
        'weight_kg' => 'decimal:2',
        'bundle_quantity' => 'integer',
        'piece_count' => 'integer',
        'amount' => 'decimal:2',
    ];

    public function order(): BelongsTo
    {
        return $this->belongsTo(ShopOrder::class, 'shop_order_id');
    }
}
