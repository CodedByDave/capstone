<?php

namespace App\Http\Middleware;

use App\Repositories\RiderRepository;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Lets only an active driver (a staff account whose employee is a rider) into the driver app,
 * and hands the rider to the request. Everyone else gets the same 404 as a missing page.
 */
class EnsureDriver
{
    public function __construct(private readonly RiderRepository $riders) {}

    public function handle(Request $request, Closure $next): Response
    {
        $rider = $this->riders->findActiveForUser($request->user()->id);

        abort_if($rider === null, 404);

        $request->attributes->set('rider', $rider);

        return $next($request);
    }
}
