<?php

namespace App\Http\Responses;

use App\Enums\AccountType;
use Laravel\Fortify\Contracts\VerifyEmailResponse as VerifyEmailResponseContract;

class VerifyEmailResponse implements VerifyEmailResponseContract
{
    public function toResponse($request)
    {
        return redirect()->intended(
            route(AccountType::dashboardRouteFor($request->user()->role)).'?verified=1',
        );
    }
}
