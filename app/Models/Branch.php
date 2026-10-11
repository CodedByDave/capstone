<?php

namespace App\Models;

use App\Casts\PhilippinePhoneCast;
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
        'block_street',
        'municipality',
        'barangay',
        'postal_code',
        'mayors_permit_number',
        'mayors_permit_path',
        'mayors_expiry_date',
        'sanitary_permit_number',
        'sanitary_permit_path',
        'sanitary_expiry_date',
        'permit_status',
        'opened_at',
        'status',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'phone' => PhilippinePhoneCast::class,
        'opened_at' => 'date',
        'mayors_expiry_date' => 'date',
        'sanitary_expiry_date' => 'date',
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
