<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Which order status changes the system may make for one shop. Only used while the shop has a paid plan.
 */
class ShopOrderAutomationSetting extends Model
{
    public const DEFAULT_GRACE_MINUTES = 120;

    public const MAX_GRACE_MINUTES = 1440;

    protected $fillable = [
        'shop_id',
        'start_on_weigh_in',
        'start_on_payment',
        'auto_complete',
        'auto_complete_grace_minutes',
        'remind_overdue',
    ];

    protected $casts = [
        'start_on_weigh_in' => 'boolean',
        'start_on_payment' => 'boolean',
        'auto_complete' => 'boolean',
        'auto_complete_grace_minutes' => 'integer',
        'remind_overdue' => 'boolean',
    ];

    /** What a shop gets before it has chosen anything: everything off. */
    public static function defaults(): array
    {
        return [
            'start_on_weigh_in' => false,
            'start_on_payment' => false,
            'auto_complete' => false,
            'auto_complete_grace_minutes' => self::DEFAULT_GRACE_MINUTES,
            'remind_overdue' => false,
        ];
    }

    public function shop(): BelongsTo
    {
        return $this->belongsTo(Shop::class);
    }
}
