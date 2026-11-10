<?php

namespace App\Http\Controllers\SupplierPortal;

use App\Http\Controllers\Controller;
use App\Http\Middleware\EnsureActiveSupplier;
use App\Http\Requests\SupplierPortal\SupplierDashboardRequest;
use App\Services\SupplierPortalService;
use Inertia\Inertia;
use Inertia\Response;

class SupplierDashboardController extends Controller
{
    public function __construct(private readonly SupplierPortalService $portal) {}

    public function __invoke(SupplierDashboardRequest $request): Response
    {
        return Inertia::render('supplier/Dashboard', $this->portal->dashboardData(
            $request->attributes->get(EnsureActiveSupplier::ATTRIBUTE),
            $request->filters(),
            $request->sort(),
        ));
    }
}
