<?php

namespace App\Http\Responses;

use Illuminate\Http\JsonResponse;
use Laravel\Fortify\Contracts\LoginResponse as LoginResponseContract;
use Symfony\Component\HttpFoundation\Response;

class LoginResponse implements LoginResponseContract
{
    public function toResponse($request): Response
    {
        // Get the authenticated user from the request
        $user = $request->user();

        // Role-based redirect
        $home = match($user->role) {
            'super_admin' => route('admin.dashboard'),
            'owner' => route('shop.dashboard'),
            'user' => route('dashboard'),
            default => route('home'),
        };

        return $request->wantsJson()
            ? new JsonResponse('', 204)
            : redirect()->intended($home);
    }
}
