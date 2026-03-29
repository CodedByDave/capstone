<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

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
        'status',
        'total_price',
        'expires_at',
        'plan_name',
        'billing_months',
        'payment_method'
    ];

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
