<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

use App\Models\EmployeeSchedule;

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
        'hire_date',
        'salary',
        'status',
    ];

    protected $casts = [
        'hire_date' => 'date:Y-m-d',
        'salary'     => 'decimal:2',
        'deleted_at' => 'datetime',
    ];

    // ─── Relationships ────────────────────────────────────────────────────────

    public function roles()
    {
        return $this->hasMany(EmployeeRole::class);
    }

    public function hasRole(string $role): bool
    {
        return $this->roles->pluck('role')->contains($role);
    }

    public function shop(): BelongsTo
    {
        return $this->belongsTo(Shop::class);
    }

    public function schedule()
    {
        return $this->hasOne(EmployeeSchedule::class, 'employee_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

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

    public function getFullNameAttribute(): string
    {
        return "{$this->first_name} {$this->last_name}";
    }
}
