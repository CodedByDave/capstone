<?php

namespace App\Models;

use App\Casts\PhilippinePhoneCast;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EmployeeArchive extends Model
{
    protected $fillable = [
        'shop_id',
        'user_id',
        'employee_id_ref',
        'employee_id',
        'first_name',
        'last_name',
        'phone',
        'address',
        'branch_name',
        'position',
        'employment_type',
        'pay_rate',
        'pay_basis',
        'hire_date',
        'status',
        'original_created_at',
        'archived_at',
    ];

    protected $casts = [
        'phone' => PhilippinePhoneCast::class,
        'hire_date' => 'date',
        'pay_rate' => 'decimal:2',
        'archived_at' => 'datetime',
        'original_created_at' => 'datetime',
    ];

    public function shop(): BelongsTo
    {
        return $this->belongsTo(Shop::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
