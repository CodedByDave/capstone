<?php

namespace App\Http\Responses;

use App\Enums\AccountType;
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

        $this->loginLogService->logSuccess(
            userId: $user->id,
            email: $user->email,
            name: $user->name,
            role: $user->role,
            ip: $request->ip(),
            userAgent: $request->userAgent() ?? '',
        );

        // Redirect to checkout flow if there's a pending plan selection
        if (session()->has('checkout')) {
            return redirect()->route('checkout.confirm');
        }

        $url = route(AccountType::dashboardRouteFor($user->role));

        return $request->wantsJson()
            ? response()->json(['two_factor' => false])
            : redirect()->intended($url);
    }
}
