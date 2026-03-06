<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ShopRole extends Model
{
    protected $fillable = [
        'shop_id',
        'name',
        'is_default'
    ];

    public function shop()
    {
        return $this->belongsTo(Shop::class);
    }
}
