<?php

namespace App\Http\Middleware;

use App\Services\PlatformRoleService;
use Closure;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnforceOwnerPlatformPermissions
{
    public function __construct(
        private readonly PlatformRoleService $roles,
    ) {}

    public function handle(Request $request, Closure $next): Response
    {
        if ($this->roles->canAccessOwnerPath($request->path())) {
            return $next($request);
        }

        return $this->deniedResponse($request);
    }

    private function deniedResponse(Request $request): JsonResponse|RedirectResponse
    {
        $message = 'Your role does not have permission to access this feature.';

        if ($request->expectsJson() || ! $request->isMethod('GET')) {
            return response()->json(['message' => $message], 403);
        }

        return redirect()->route('landing')->with('toast', [
            'type' => 'error',
            'message' => $message,
        ]);
    }
}
