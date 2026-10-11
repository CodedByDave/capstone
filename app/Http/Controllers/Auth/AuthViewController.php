<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Services\AuthNavigationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class AuthViewController extends Controller
{
    public function __construct(private readonly AuthNavigationService $navigationService) {}

    public function verificationNotice(Request $request): Response|RedirectResponse
    {
        $dashboardRoute = $this->navigationService->verifiedDashboardRoute($request->user());

        return $dashboardRoute
            ? redirect()->route($dashboardRoute)
            : Inertia::render('auth/VerifyEmail', [
                'status' => $request->session()->get('status'),
            ]);
    }

    public function confirmPassword(): Response
    {
        return Inertia::render('auth/ConfirmPassword');
    }
}
