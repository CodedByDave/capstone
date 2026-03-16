<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Branch extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'shop_id',
        'branch_code',
        'name',
        'phone',
        'email',
        'manager_name',
        'address',
        'opened_at',
        'status',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'opened_at' => 'date',
    ];

    // Relationship

    public function shop(): BelongsTo
    {
        return $this->belongsTo(Shop::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    // employees that belong to this branch
    public function employees(): HasMany
    {
        return $this->hasMany(Employee::class, 'branch_name', 'name');
    }

    // Scopes

    public function scopeActive($query)
    {
        return $query->where('status', 'Active');
    }

    public function scopeForShop($query, int $shopId)
    {
        return $query->where('shop_id', $shopId);
    }
}
