<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Fortify\TwoFactorAuthenticatable;

class User extends Authenticatable
{
    use HasFactory, Notifiable, TwoFactorAuthenticatable, SoftDeletes; // ← add SoftDeletes

    public const ROLE_USER        = 'user';
    public const ROLE_OWNER       = 'owner';
    public const ROLE_SUPER_ADMIN = 'super_admin';
    public const ROLE_MANAGER     = 'manager';
    public const ROLE_STAFF       = 'staff';

    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'shop_id',
        'otp_code',
        'otp_expires_at',
        'is_verified',
    ];

    public function isOwner(): bool
    {
        return $this->role === self::ROLE_OWNER;
    }

    public function isManager(): bool
    {
        return $this->role === self::ROLE_MANAGER;
    }

    public function isStaff(): bool
    {
        return $this->role === self::ROLE_STAFF;
    }

    public function isSuperAdmin(): bool
    {
        return $this->role === self::ROLE_SUPER_ADMIN;
    }

    public function isUser(): bool
    {
        return $this->role === self::ROLE_USER;
    }

    public function shop()
    {
        return $this->hasOne(Shop::class, 'owner_id');
    }
    public function orders()
    {
        return $this->hasMany(Order::class);
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
            'email_verified_at'        => 'datetime',
            'password'                 => 'hashed',
            'two_factor_confirmed_at'  => 'datetime',
            'otp_expires_at'           => 'datetime',
            'is_verified'              => 'boolean',
        ];
    }
}
