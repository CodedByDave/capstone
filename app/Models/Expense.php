<?php

namespace App\Models;

use App\Traits\HasPublicId;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Expense extends Model
{
    use HasPublicId, SoftDeletes;

    public const SOURCE_ORDER_SUPPLIES = 'order_supplies';

    public const SOURCE_ORDER_REFUND = 'order_refund';

    public const SOURCE_PAYROLL = 'payroll';

    protected $fillable = [
        'shop_id',
        'category_id',
        'shop_order_id',
        'payroll_id',
        'source',
        'title',
        'amount',
        'expense_date',
        'description',
        'reference_number',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'expense_date' => 'date',
    ];

    public function shop(): BelongsTo
    {
        return $this->belongsTo(Shop::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(ExpenseCategory::class, 'category_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }
}
