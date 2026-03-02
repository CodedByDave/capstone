<?php

namespace App\Models;

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
        'hire_date',
        'salary',
        'status',
        'original_created_at',
        'archived_at',
    ];

    protected $casts = [
        'hire_date'           => 'date',
        'salary'              => 'decimal:2',
        'archived_at'         => 'datetime',
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
