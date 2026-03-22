<?php

namespace App\Http\Responses;

use App\Services\LoginLogService;
use Illuminate\Support\Facades\Auth;
use Laravel\Fortify\Contracts\LoginResponse as LoginResponseContract;

class LoginResponse implements LoginResponseContract
{
    public function __construct(
        private readonly LoginLogService $loginLogService,
    ) {}

    public function toResponse($request)
    {
        $user = Auth::user();

        // Log successful login here
        $this->loginLogService->logSuccess(
            userId:    $user->id,
            email:     $user->email,
            name:      $user->name,
            role:      $user->role,
            ip:        $request->ip(),
            userAgent: $request->userAgent() ?? '',
        );

        // Redirect based on role
        $url = match ($user->role) {
            'super_admin' => route('admin.dashboard'),
            'owner'       => route('shop.dashboard'),
            'staff',      => route('staff.dashboard'),
            default       => route('home')
        };

        return $request->wantsJson()
            ? response()->json(['two_factor' => false])
            : redirect()->intended($url);
    }
}
