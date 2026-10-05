<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CustomerAgreementAcceptance extends Model
{
    protected $fillable = [
        'shop_order_id', 'shop_id', 'customer_user_id', 'accepted_by_user_id',
        'acceptance_method', 'customer_name', 'agreement_version', 'agreement_title',
        'agreement_content', 'content_hash', 'accepted_at', 'ip_address', 'user_agent',
    ];

    protected $casts = ['accepted_at' => 'datetime'];

    public function order(): BelongsTo
    {
        return $this->belongsTo(ShopOrder::class, 'shop_order_id');
    }

    public function shop(): BelongsTo
    {
        return $this->belongsTo(Shop::class);
    }
}
