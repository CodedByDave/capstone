<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

/** A text the shop sent to a customer through its SMS gateway, kept so staff can see what went out. */
class SmsMessage extends Model
{
    public const PURPOSE_ORDER_READY = 'order_ready';

    public const PURPOSE_TEST = 'test';

    public const STATUS_SENT = 'sent';

    public const STATUS_FAILED = 'failed';

    protected $fillable = [
        'shop_id', 'shop_order_id', 'purpose', 'provider', 'phone', 'message', 'status', 'gateway_message_id', 'error', 'sent_by',
    ];

    protected static function booted(): void
    {
        static::creating(function (SmsMessage $sms): void {
            $sms->public_id ??= (string) Str::ulid();
        });
    }

    public function sender(): BelongsTo
    {
        return $this->belongsTo(User::class, 'sent_by');
    }

    public function isSent(): bool
    {
        return $this->status === self::STATUS_SENT;
    }
}
