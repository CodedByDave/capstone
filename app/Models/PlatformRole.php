<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class PlatformRole extends Model
{
    protected $fillable = ['name', 'slug', 'description', 'is_system'];

    protected $casts = ['is_system' => 'boolean'];

    protected static function booted(): void
    {
        static::creating(function (PlatformRole $role) {
            $role->public_id ??= (string) Str::ulid();
        });
    }

    public function getRouteKeyName(): string
    {
        return 'public_id';
    }

    public function permissions(): HasMany
    {
        return $this->hasMany(PlatformRolePermission::class);
    }
}
