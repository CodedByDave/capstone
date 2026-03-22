<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LowStockAlert extends Model
{
    protected $fillable = [
        'inventory_id',
        'shop_id',
        'quantity_at_alert',
        'min_stock_at_alert',
        'status',
        'resolved_at',
    ];

    protected $casts = [
        'quantity_at_alert'  => 'integer',
        'min_stock_at_alert' => 'integer',
        'resolved_at'        => 'datetime',
    ];

    // Relationships

    public function inventory(): BelongsTo
    {
        return $this->belongsTo(Inventory::class);
    }

    public function shop(): BelongsTo
    {
        return $this->belongsTo(Shop::class);
    }

    //Scopes

    public function scopeUnread($query)
    {
        return $query->where('status', 'unread');
    }

    public function scopeForShop($query, int $shopId)
    {
        return $query->where('shop_id', $shopId);
    }

    // Helpers

    public function markAsRead(): void
    {
        $this->update(['status' => 'read']);
    }

    public function resolve(): void
    {
        $this->update([
            'status'      => 'resolved',
            'resolved_at' => now(),
        ]);
    }

    public function dismiss(): void
    {
        $this->update(['status' => 'dismissed']);
    }
}
