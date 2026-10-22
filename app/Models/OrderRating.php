<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * A customer's star rating of one completed order.
 */
class OrderRating extends Model
{
    public const MIN = 1;

    public const MAX = 5;

    protected $fillable = ['shop_order_id', 'shop_id', 'user_id', 'rating', 'comment'];

    protected $casts = ['rating' => 'integer'];

    public function order(): BelongsTo
    {
        return $this->belongsTo(ShopOrder::class, 'shop_order_id');
    }

    public function shop(): BelongsTo
    {
        return $this->belongsTo(Shop::class);
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
