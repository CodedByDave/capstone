<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'branch_name',
        'shop_name',
        'owner_name',
        'email',
        'phone',
        'block_street',
        'municipality',
        'barangay',
        'postal_code',
        'status',
        'total_price',
        'expires_at',
        'subscription_plan'
    ];

<<<<<<< HEAD
=======
    protected $casts = [
        'is_upgrade' => 'boolean',
        'is_trial' => 'boolean',
        'bir_expiry_date' => 'date',
        'dti_expiry_date' => 'date',
        'mayors_expiry_date' => 'date',
        'sanitary_expiry_date' => 'date',
        'expires_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::creating(function (Order $order) {
            $order->public_id ??= (string) Str::ulid();
            $order->transaction_reference ??= 'TXN-'.Str::upper((string) Str::ulid());
        });
    }

>>>>>>> 7b1b8656 (feat(admin): added issue reports features for system users and RBAC for giving users access what they can do)
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

    /**
     * Paid subscriptions are active. Free trials are the only subscriptions
     * that become active without a payment.
     */
    public function scopeActiveSubscription(Builder $query): Builder
    {
        return $query->where(function (Builder $query) {
            $query->where('status', 'paid')
                ->orWhere(function (Builder $query) {
                    $query->where('status', 'approved')
                        ->where('is_trial', true);
                });
        });
    }
}
