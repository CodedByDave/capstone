<?php

namespace App\Http\Middleware;

use App\Services\SupplierPortalService;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

/**
 * Lets a supplier login in only while the shop keeps its supplier record active. The resolved
 * record is shared with the controllers as the "supplier" request attribute.
 */
class EnsureActiveSupplier
{
    public const ATTRIBUTE = 'supplier';

    public function __construct(private readonly SupplierPortalService $portal) {}

    public function handle(Request $request, Closure $next): Response
    {
        $supplier = $request->user() ? $this->portal->activeSupplierFor($request->user()) : null;

        if ($supplier === null) {
            Auth::guard('web')->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('login')->with('toast', [
                'type' => 'error',
                'message' => 'Your supplier account is not active. Please contact the laundry shop you supply.',
            ]);
        }

        $request->attributes->set(self::ATTRIBUTE, $supplier);

        return $next($request);
    }
}
