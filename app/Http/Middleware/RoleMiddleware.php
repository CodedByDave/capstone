<?php

namespace App\Http\Middleware;

use App\Enums\AccountType;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    public function handle(Request $request, Closure $next, $role): Response
    {
        $user = Auth::user();

        if (! $user) {
            return redirect()->route('login');
        }

        // Log for debugging
        Log::info('RoleMiddleware Check', [
            'user_id' => $user->id,
            'user_email' => $user->email,
            'user_role' => $user->role,
            'required_role' => $role,
            'match' => $user->role === $role,
        ]);

        if ($user->role !== $role) {
            // Redirect based on actual role instead of 404
            $accountType = AccountType::tryFrom($user->role ?? '');

            return $accountType
                ? redirect()->route($accountType->dashboardRoute())
                : abort(403, 'Unauthorized access');
        }

        return $next($request);
    }
}
