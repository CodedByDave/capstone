<?php

namespace App\Models;

use App\Enums\AccountType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Fortify\TwoFactorAuthenticatable;

class User extends Authenticatable
{
    use HasFactory, Notifiable, SoftDeletes, TwoFactorAuthenticatable;

    protected $fillable = [
        'name',
        'email',
        'email_verified_at',
        'password',
        'role',
        'shop_id',
        'otp_code',
        'otp_expires_at',
        'google_id',
    ];

    public function isOwner(): bool
    {
        return $this->role === AccountType::ShopOwner->value;
    }

    public function isStaff(): bool
    {
        return $this->role === AccountType::Staff->value;
    }

    public function isSuperAdmin(): bool
    {
        return $this->role === AccountType::SuperAdmin->value;
    }

    public function isUser(): bool
    {
        return $this->role === AccountType::Customer->value;
    }

    public function shop()
    {
        return $this->hasOne(Shop::class, 'owner_id');
    }

    public function orders()
    {
        return $this->hasMany(Order::class);
    }

    public function hasActiveSubscription(): bool
    {
        return $this->subscriptions()
            ->where('status', 'active')
            ->exists();
    }

    protected $hidden = [
        'password',
        'two_factor_secret',
        'two_factor_recovery_codes',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'two_factor_confirmed_at' => 'datetime',
            'otp_expires_at' => 'datetime',
        ];
    }
}
