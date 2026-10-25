<?php

namespace App\Http\Middleware;

use App\Repositories\RiderRepository;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/** A driver who lands on the staff dashboard (for example right after login) goes to the driver app. */
class RedirectDriverToApp
{
    public function __construct(private readonly RiderRepository $riders) {}

    public function handle(Request $request, Closure $next): Response
    {
        if ($request->user() && $this->riders->findActiveForUser($request->user()->id)) {
            return redirect()->route('driver.home');
        }

        return $next($request);
    }
}
