<?php

namespace App\Http\Controllers\SupplierPortal;

use App\Http\Controllers\Controller;
use App\Http\Middleware\EnsureActiveSupplier;
use App\Http\Requests\SupplierPortal\AnswerPurchaseOrderRequest;
use App\Http\Requests\SupplierPortal\DeliverPurchaseOrderRequest;
use App\Services\PurchaseOrderService;
use App\Services\SupplierPortalService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;

class SupplierPurchaseOrderController extends Controller
{
    public function __construct(
        private readonly PurchaseOrderService $orders,
        private readonly SupplierPortalService $portal,
    ) {}

    /** Orders the shop sent this supplier. */
    public function index(Request $request): Response
    {
        $supplier = $request->attributes->get(EnsureActiveSupplier::ATTRIBUTE);

        return Inertia::render('supplier/PurchaseOrders', [
            ...$this->portal->identityFor($supplier),
            'purchaseOrders' => $this->orders->portalData($supplier),
        ]);
    }

    /** The supplier hands over the delivery and attaches their photo of it. */
    public function deliver(DeliverPurchaseOrderRequest $request, string $purchaseOrder): RedirectResponse
    {
        $supplier = $request->attributes->get(EnsureActiveSupplier::ATTRIBUTE);
        $order = $this->orders->deliverBySupplier($supplier, $purchaseOrder, $request->file('photo'));

        return back()->with('toast', ['type' => 'success', 'message' => "Order {$order->po_number} marked delivered. The shop has been told."]);
    }

    /** The supplier's own delivery photo, or the one the shop took on receipt. */
    public function proof(Request $request, string $purchaseOrder, string $kind)
    {
        $supplier = $request->attributes->get(EnsureActiveSupplier::ATTRIBUTE);

        return Storage::disk(PurchaseOrderService::PROOF_DISK)
            ->response($this->orders->proofPathForSupplier($supplier, $purchaseOrder, $kind));
    }

    /** The supplier confirms or declines an order the shop sent them. */
    public function answer(AnswerPurchaseOrderRequest $request, string $purchaseOrder): RedirectResponse
    {
        $supplier = $request->attributes->get(EnsureActiveSupplier::ATTRIBUTE);

        if ($request->validated('answer') === 'confirm') {
            $order = $this->orders->confirmBySupplier(
                $supplier,
                $purchaseOrder,
                $request->validated('expected_date'),
                $request->validated('note'),
            );

            return back()->with('toast', ['type' => 'success', 'message' => "Order {$order->po_number} confirmed. The shop has been told."]);
        }

        $order = $this->orders->declineBySupplier($supplier, $purchaseOrder, (string) $request->validated('note'));

        return back()->with('toast', ['type' => 'success', 'message' => "Order {$order->po_number} declined. The shop has been told."]);
    }
}
