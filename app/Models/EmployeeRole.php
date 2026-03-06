<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EmployeeRole extends Model
{
    protected $fillable = [
        'employee_id',
        'role'
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }
}
