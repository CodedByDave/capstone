<?php

namespace App\Http\Middleware;

use App\Services\BusinessAgreementService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureCurrentBusinessAgreementAccepted
{
    public function __construct(
        private readonly BusinessAgreementService $agreementService,
    ) {}

    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user
            && ! $request->routeIs('shop.agreement.*')
            && $this->agreementService->requiresCurrentAcceptance($user)) {
            return redirect()->route('shop.agreement.show')->with('toast', [
                'type' => 'info',
                'message' => 'Please review and accept the current business agreement to continue.',
            ]);
        }

        return $next($request);
    }
}
