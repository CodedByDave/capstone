<?php

namespace App\Http\Controllers\Shop\Inventory;

use App\Http\Controllers\Controller;
use App\Http\Requests\Shop\Inventory\PurchaseOrderFilterRequest;
use App\Http\Requests\Shop\Inventory\ReceivePurchaseOrderRequest;
use App\Http\Requests\Shop\Inventory\StorePurchaseOrderRequest;
use App\Services\PurchaseOrderService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class PurchaseOrderController extends Controller
{
    public function __construct(private readonly PurchaseOrderService $orders) {}

    private function base(Request $request): string
    {
        return $request->user()->role === 'owner' ? '/shop/purchase-orders' : '/staff/purchase-orders';
    }

    public function index(PurchaseOrderFilterRequest $request): Response
    {
        return Inertia::render('shop/inventory/purchase-orders/Index', [
            ...$this->orders->indexData($request->user(), $request->filters(), $request->sort()),
            'base' => $this->base($request),
        ]);
    }

    public function create(Request $request): Response
    {
        return Inertia::render('shop/inventory/purchase-orders/Create', [
            ...$this->orders->formData($request->user(), $request->query('supplier')),
            'base' => $this->base($request),
        ]);
    }

    public function store(StorePurchaseOrderRequest $request): RedirectResponse
    {
        $order = $this->orders->create($request->user(), $request->validated());

        return redirect("{$this->base($request)}/{$order->public_id}")->with('toast', [
            'type' => 'success',
            'message' => $order->sent_at
                ? "Purchase order {$order->po_number} sent to the supplier."
                : "Purchase order {$order->po_number} saved as a draft.",
        ]);
    }

    public function show(Request $request, string $purchaseOrder): Response
    {
        return Inertia::render('shop/inventory/purchase-orders/Show', [
            'order' => $this->orders->find($request->user(), $purchaseOrder),
            'base' => $this->base($request),
        ]);
    }

    public function send(Request $request, string $purchaseOrder): RedirectResponse
    {
        $order = $this->orders->send($request->user(), $purchaseOrder);

        return back()->with('toast', ['type' => 'success', 'message' => "Purchase order {$order->po_number} sent to the supplier."]);
    }

    public function cancel(Request $request, string $purchaseOrder): RedirectResponse
    {
        $order = $this->orders->cancel($request->user(), $purchaseOrder);

        return back()->with('toast', ['type' => 'success', 'message' => "Purchase order {$order->po_number} cancelled."]);
    }

    public function receive(ReceivePurchaseOrderRequest $request, string $purchaseOrder): RedirectResponse
    {
        $order = $this->orders->receive($request->user(), $purchaseOrder, $request->receivedQuantities());

        return back()->with('toast', [
            'type' => 'success',
            'message' => "Purchase order {$order->po_number} received. Stock and expenses were updated.",
        ]);
    }
}
