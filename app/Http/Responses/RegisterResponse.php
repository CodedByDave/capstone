<?php

namespace App\Http\Responses;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Laravel\Fortify\Contracts\RegisterResponse as RegisterResponseContract;
use Symfony\Component\HttpFoundation\Response;

class RegisterResponse implements RegisterResponseContract
{
    /**
     * Create an HTTP response that represents the object.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function toResponse($request): Response
    {
        // Preserve checkout data before session invalidation
        $checkout = session('checkout');

        Auth::guard('web')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        // Restore checkout data so it survives the redirect to login
        if ($checkout) {
            session(['checkout' => $checkout]);
        }

        return $request->wantsJson()
            ? new JsonResponse('', 201)
            : redirect()->route('login')->with('toast', [
                'type'    => 'success',
                'message' => 'Your account has been created. Please login.',
            ]);
    }
}
