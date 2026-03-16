<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class ActivityLog extends Model
{
    protected $fillable = [
        'module',
        'action',
        'subject_type',
        'subject_id',
        'performed_by',
        'shop_id',
        'changes',
    ];

    protected $casts = [
        'changes' => 'array',
    ];

    public function subject(): MorphTo
    {
        return $this->morphTo();
    }

    public function performer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'performed_by');
    }

    public function shop(): BelongsTo
    {
        return $this->belongsTo(Shop::class);
    }
}
