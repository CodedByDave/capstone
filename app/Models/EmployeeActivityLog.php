<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EmployeeActivityLog extends Model
{
    protected $fillable = [
        'employee_id',
        'performed_by',
        'action',
        'changes',
    ];

    protected $casts = [
        'changes' => 'array',
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class)->withTrashed();
    }

    public function performer()
    {
        return $this->belongsTo(User::class, 'performed_by')->withTrashed();
    }
}
