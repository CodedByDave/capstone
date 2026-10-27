<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** A service category a shop created itself, next to the built-in ones in ShopService::CATEGORIES. */
class ServiceCategory extends Model
{
    protected $fillable = ['shop_id', 'key', 'name', 'description'];

    public function shop(): BelongsTo
    {
        return $this->belongsTo(Shop::class);
    }
}
