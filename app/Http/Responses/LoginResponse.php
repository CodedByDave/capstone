<?php

namespace App\Http\Responses;

use App\Enums\AccountType;
use App\Services\BusinessAgreementService;
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

        // A newly registered owner (shop still pending) or one who owes the current
        // agreement must start at the dashboard flow; a stale "intended" URL such as
        // /shop/employee must not win.
        if ($user->role === AccountType::ShopOwner->value
            && ($user->shop?->status === 'pending'
                || app(BusinessAgreementService::class)->requiresCurrentAcceptance($user))) {
            $request->session()->forget('url.intended');
        }

        return $request->wantsJson()
            ? response()->json(['two_factor' => false])
            : redirect()->intended($url);
    }
}
