<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EmployeeSchedule extends Model
{
    protected $fillable = [
        'employee_id',
        'day',
        'start_time',
        'end_time',
    ];

    protected $casts = [
        'start_time' => 'datetime:H:i',
        'end_time' => 'datetime:H:i',
    ];

    /** An overnight shift: it ends at or before the time it starts, so it finishes the next day. */
    public function endsNextDay(): bool
    {
        return $this->start_time !== null
            && $this->end_time !== null
            && $this->end_time->format('H:i') <= $this->start_time->format('H:i');
    }

    /**
     * The employee this schedule belongs to
     */
    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'employee_id', 'id');
    }
}
