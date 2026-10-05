<?php

namespace App\Models;

use App\Casts\PhilippinePhoneCast;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Employee extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'user_id',
        'shop_id',
        'branch_name',
        'employee_id',
        'first_name',
        'last_name',
        'email',
        'phone',
        'address',
        'position',
        'employment_type',
        'pay_rate',
        'pay_basis',
        'hire_date',
        'status',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'phone' => PhilippinePhoneCast::class,
        'hire_date' => 'date:Y-m-d',
        'pay_rate' => 'decimal:2',
        'deleted_at' => 'datetime',
    ];

    // Relationships
    public function roles(): HasMany
    {
        return $this->hasMany(EmployeeRole::class);
    }

    public function shop(): BelongsTo
    {
        return $this->belongsTo(Shop::class);
    }

    public function schedules(): HasMany
    {
        return $this->hasMany(EmployeeSchedule::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function activityLogs(): HasMany
    {
        return $this->hasMany(EmployeeActivityLog::class)->latest();
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('status', 'Active');
    }

    public function scopeInactive($query)
    {
        return $query->where('status', 'Inactive');
    }

    public function scopeByBranch($query, string $branch)
    {
        return $query->where('branch_name', $branch);
    }

    // Accessors
    public function getFullNameAttribute(): string
    {
        return "{$this->first_name} {$this->last_name}";
    }
}
