<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Shop extends Model
{
    use SoftDeletes;
    protected $fillable = [
        'owner_id',
        'shop_name',
        'branch_name',
        'phone',
        'block_street',
        'municipality',
        'barangay',
        'postal_code',
        'status',
        'disable_reason',
        'deduct_sss',
        'deduct_philhealth',
        'deduct_pagibig',
        'deduct_withholding_tax',
    ];

    protected $casts = [
        'deduct_sss'             => 'boolean',
        'deduct_philhealth'      => 'boolean',
        'deduct_pagibig'         => 'boolean',
        'deduct_withholding_tax' => 'boolean',
    ];

    public function latestOrder()
    {
        return $this->hasOne(Order::class, 'user_id', 'user_id')
            ->where('status', 'paid')
            ->latestOfMany();
    }
    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function order()
    {
        return $this->hasOne(Order::class, 'user_id', 'owner_id');
    }

    public function employees(): HasMany
    {
        return $this->hasMany(Employee::class);
    }

    public function getBranchNames(): array
    {
        $branches = $this->employees()
            ->whereNotNull('branch_name')
            ->distinct()
            ->pluck('branch_name')
            ->toArray();

        // Include the shop's own branch name if set
        if ($this->branch_name && !in_array($this->branch_name, $branches)) {
            array_unshift($branches, $this->branch_name);
        }

        return $branches;
    }
}
