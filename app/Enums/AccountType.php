<?php

namespace App\Enums;

enum AccountType: string
{
    case Customer = 'user';
    case ShopOwner = 'owner';
    case Staff = 'staff';
    case SuperAdmin = 'super_admin';

    public function dashboardRoute(): string
    {
        return match ($this) {
            self::Customer => 'user.dashboard',
            self::ShopOwner => 'shop.dashboard',
            self::Staff => 'staff.dashboard',
            self::SuperAdmin => 'admin.dashboard',
        };
    }

    public static function dashboardRouteFor(?string $type): string
    {
        return self::tryFrom($type ?? '')?->dashboardRoute() ?? 'landing';
    }
}
