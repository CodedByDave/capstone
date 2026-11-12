<?php

namespace App\Http\Controllers\SupplierPortal;

use App\Http\Controllers\Controller;
use App\Http\Middleware\EnsureActiveSupplier;
use App\Http\Requests\SupplierPortal\SupplierDashboardRequest;
use App\Services\PurchaseOrderService;
use App\Services\SupplierPortalService;
use Inertia\Inertia;
use Inertia\Response;

class SupplierDashboardController extends Controller
{
    public function __construct(
        private readonly SupplierPortalService $portal,
        private readonly PurchaseOrderService $purchaseOrders,
    ) {}

    public function __invoke(SupplierDashboardRequest $request): Response
    {
        $supplier = $request->attributes->get(EnsureActiveSupplier::ATTRIBUTE);

        return Inertia::render('supplier/Dashboard', [
            ...$this->portal->dashboardData($supplier, $request->filters(), $request->sort()),
            'purchaseOrders' => $this->purchaseOrders->portalData($supplier),
        ]);
    }
}
