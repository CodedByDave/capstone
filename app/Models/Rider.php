<?php

namespace App\Models;

use App\Casts\PhilippinePhoneCast;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Rider extends Model
{
    protected $fillable = [
        'shop_id', 'employee_id', 'name', 'phone', 'vehicle_type', 'status', 'is_online', 'available_since',
    ];

    protected $casts = [
        'phone' => PhilippinePhoneCast::class,
        'is_online' => 'boolean',
        'available_since' => 'datetime',
    ];

    /**
     * Which vehicle to draw on the tracking map. The shop types the vehicle freely, so this is a
     * best guess from the text; motorcycles are the common case and the default.
     */
    public function vehicleKind(): string
    {
        return preg_match('/\b(car|van|truck|sedan|suv|jeep|l300|vehicle|auto)\b/i', (string) $this->vehicle_type) === 1
            ? 'car'
            : 'motorcycle';
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function shop(): BelongsTo
    {
        return $this->belongsTo(Shop::class);
    }

    public function deliveries(): HasMany
    {
        return $this->hasMany(Delivery::class);
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }
}
