<?php

namespace App\Http\Controllers\SupplierPortal;

use App\Http\Controllers\Controller;
use App\Http\Middleware\EnsureActiveSupplier;
use App\Http\Requests\SupplierPortal\SupplierDashboardRequest;
use App\Services\SupplierPortalService;
use Inertia\Inertia;
use Inertia\Response;

class SupplierSuppliesController extends Controller
{
    public function __construct(private readonly SupplierPortalService $portal) {}

    public function index(SupplierDashboardRequest $request): Response
    {
        $supplier = $request->attributes->get(EnsureActiveSupplier::ATTRIBUTE);

        return Inertia::render('supplier/Supplies', $this->portal->suppliesData($supplier, $request->filters(), $request->sort()));
    }
}
