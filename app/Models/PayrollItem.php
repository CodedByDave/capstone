<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PayrollItem extends Model
{
    protected $fillable = [
        'payroll_id',
        'employee_id',
        'basic_salary',
        'days_worked',
        'days_absent',
        'days_late',
        'days_half_day',
        'deductions',
        'bonuses',
        'net_pay',
        'remarks',
    ];

    protected $casts = [
        'basic_salary' => 'decimal:2',
        'deductions'   => 'decimal:2',
        'bonuses'      => 'decimal:2',
        'net_pay'      => 'decimal:2',
    ];

    public function payroll(): BelongsTo
    {
        return $this->belongsTo(Payroll::class);
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }
}
