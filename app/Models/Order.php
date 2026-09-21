<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'shop_name',
        'owner_name',
        'email',
        'phone',
        'block_street',
        'municipality',
        'barangay',
        'postal_code',
        'kyc_bir',
        'kyc_dti',
        'kyc_mayors',
        'kyc_sanitary',
        'bir_expiry_date',
        'dti_expiry_date',
        'mayors_expiry_date',
        'sanitary_expiry_date',
        'status',
        'rejection_reason',
        'total_price',
        'expires_at',
        'plan_name',
        'billing_months',
        'is_upgrade',
        'is_trial',
        'payment_method',
    ];

    protected $casts = [
        'is_upgrade' => 'boolean',
        'is_trial' => 'boolean',
        'bir_expiry_date' => 'date',
        'dti_expiry_date' => 'date',
        'mayors_expiry_date' => 'date',
        'sanitary_expiry_date' => 'date',
    ];

    protected static function booted(): void
    {
        static::creating(function (Order $order) {
            $order->public_id ??= (string) Str::ulid();
            $order->transaction_reference ??= 'TXN-'.Str::upper((string) Str::ulid());
        });
    }

    public function modules(): HasMany
    {
        return $this->hasMany(OrderModule::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
