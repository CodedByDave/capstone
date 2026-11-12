<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** One supply on a purchase order, with the price it was ordered at. */
class PurchaseOrderItem extends Model
{
    protected $fillable = [
        'purchase_order_id', 'supplier_item_id', 'inventory_id', 'name', 'unit', 'quantity', 'unit_price',
        'line_total', 'stock_per_unit', 'received_quantity',
    ];

    protected $casts = [
        'quantity' => 'decimal:2',
        'unit_price' => 'decimal:2',
        'line_total' => 'decimal:2',
        'stock_per_unit' => 'integer',
        'received_quantity' => 'decimal:2',
    ];

    public function purchaseOrder(): BelongsTo
    {
        return $this->belongsTo(PurchaseOrder::class);
    }

    public function inventory(): BelongsTo
    {
        return $this->belongsTo(Inventory::class)->withTrashed();
    }
}
