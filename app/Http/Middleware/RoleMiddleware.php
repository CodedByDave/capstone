<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class RoleMiddleware
{
    public function handle(Request $request, Closure $next, $role): Response
    {
        $user = Auth::user();

        if (!$user) {
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
            return match($user->role) {
                'super_admin' => redirect()->route('admin.dashboard'),
                'owner'       => redirect()->route('shop.dashboard'),
                'staff'       => redirect()->route('staff.dashboard'),
                'user'        => redirect()->route('user.dashboard'),
                default       => abort(403, 'Unauthorized access'),
            };
        }

        return $next($request);
    }
}
