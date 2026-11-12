<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

/** An order a shop places with one of its own suppliers. */
class PurchaseOrder extends Model
{
    public const STATUS_DRAFT = 'draft';

    public const STATUS_SENT = 'sent';

    public const STATUS_CONFIRMED = 'confirmed';

    public const STATUS_DECLINED = 'declined';

    public const STATUS_RECEIVED = 'received';

    public const STATUS_CANCELLED = 'cancelled';

    public const STATUSES = [
        self::STATUS_DRAFT,
        self::STATUS_SENT,
        self::STATUS_CONFIRMED,
        self::STATUS_DECLINED,
        self::STATUS_RECEIVED,
        self::STATUS_CANCELLED,
    ];

    protected $fillable = [
        'shop_id', 'supplier_id', 'po_number', 'status', 'total', 'notes', 'expected_date', 'supplier_note',
        'created_by', 'received_by', 'sent_at', 'confirmed_at', 'declined_at', 'received_at', 'cancelled_at',
    ];

    protected $casts = [
        'total' => 'decimal:2',
        'expected_date' => 'date',
        'sent_at' => 'datetime',
        'confirmed_at' => 'datetime',
        'declined_at' => 'datetime',
        'received_at' => 'datetime',
        'cancelled_at' => 'datetime',
    ];

    protected $hidden = ['id', 'shop_id', 'supplier_id', 'created_by', 'received_by'];

    protected static function booted(): void
    {
        static::creating(function (PurchaseOrder $order): void {
            $order->public_id ??= (string) Str::ulid();
            $order->po_number ??= 'PO-'.now()->format('Ymd').'-'.strtoupper(Str::random(5));
        });
    }

    public function getRouteKeyName(): string
    {
        return 'public_id';
    }

    public function shop(): BelongsTo
    {
        return $this->belongsTo(Shop::class);
    }

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class)->withTrashed();
    }

    public function items(): HasMany
    {
        return $this->hasMany(PurchaseOrderItem::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function receiver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'received_by');
    }
}
