<?php

namespace App\Actions\Fortify;

use App\Services\LoginLogService;
use Illuminate\Auth\Events\Failed;
use Illuminate\Contracts\Auth\StatefulGuard;
use Illuminate\Validation\ValidationException;
use Laravel\Fortify\Actions\AttemptToAuthenticate as FortifyAttemptToAuthenticate;
use Laravel\Fortify\Fortify;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;

class AttemptToAuthenticate
{
    public function __construct(
        private readonly StatefulGuard $guard,
        private readonly LoginLogService $loginLogService,
    ) {}

    public function handle(Request $request, callable $next)
    {
        $credentials = [
            Fortify::username() => $request->input(Fortify::username()),
            'password'          => $request->input('password'),
        ];

        if ($this->guard->attempt($credentials, $request->boolean('remember'))) {
            return $next($request);
        }

        // Log failed attempt
        $user = User::where(Fortify::username(), $request->input(Fortify::username()))->first();

        $this->loginLogService->logFailed(
            userId: $user?->id,
            email: $request->input(Fortify::username()),
            name: $user?->name,
            role: $user?->role,
            ip: $request->ip(),
            userAgent: $request->userAgent() ?? '',
        );

        throw ValidationException::withMessages([
            Fortify::username() => ['Email or password is invalid.'],
        ]);
    }
}
