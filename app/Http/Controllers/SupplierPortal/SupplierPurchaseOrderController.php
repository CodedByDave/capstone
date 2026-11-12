<?php

namespace App\Http\Controllers\SupplierPortal;

use App\Http\Controllers\Controller;
use App\Http\Middleware\EnsureActiveSupplier;
use App\Http\Requests\SupplierPortal\AnswerPurchaseOrderRequest;
use App\Services\PurchaseOrderService;
use Illuminate\Http\RedirectResponse;

class SupplierPurchaseOrderController extends Controller
{
    public function __construct(private readonly PurchaseOrderService $orders) {}

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
