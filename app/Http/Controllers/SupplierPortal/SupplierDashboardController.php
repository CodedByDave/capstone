<?php

namespace App\Http\Controllers\SupplierPortal;

use App\Http\Controllers\Controller;
use App\Http\Middleware\EnsureActiveSupplier;
use App\Services\PurchaseOrderService;
use App\Services\SupplierPortalService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class SupplierDashboardController extends Controller
{
    public function __construct(
        private readonly SupplierPortalService $portal,
        private readonly PurchaseOrderService $purchaseOrders,
    ) {}

    public function __invoke(Request $request): Response
    {
        $supplier = $request->attributes->get(EnsureActiveSupplier::ATTRIBUTE);

        return Inertia::render('supplier/Dashboard', [
            ...$this->portal->overviewData($supplier),
            'purchaseOrders' => $this->purchaseOrders->portalData($supplier),
        ]);
    }
}
