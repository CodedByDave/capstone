<?php

namespace App\Services;

use App\Enums\AccountType;
use App\Models\User;

class AuthNavigationService
{
    public function verifiedDashboardRoute(User $user): ?string
    {
        return $user->hasVerifiedEmail()
            ? AccountType::dashboardRouteFor($user->role)
            : null;
    }
}
