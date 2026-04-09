<?php

namespace App\Http\Controllers\Shop\Operations;

use App\Http\Controllers\Controller;
use App\Http\Requests\Shop\Operations\StoreShopOrderRequest;
use App\Http\Requests\Shop\Operations\UpdateShopOrderRequest;
use App\Models\ShopOrder;
use App\Models\ShopServicePricing;
use App\Models\Inventory;
use App\Services\ShopOrderService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ShopOrderController extends Controller
{
    public function __construct(protected ShopOrderService $service) {}

    public function index(Request $request): Response
    {
        $shopId      = auth()->user()->shop->id;
        $showTrashed = $request->boolean('archived');

        $orders = $this->service->getPaginatedOrders(
            shopId: $shopId,
            perPage: 15,
            filters: $request->only(['status', 'payment_status', 'search']),
            onlyTrashed: $showTrashed,
        );

        return Inertia::render('shop/operations/orders/Index', [
            'orders'        => $orders,
            'statusSummary' => $this->service->getStatusSummary($shopId),
            'filters'       => $request->only(['status', 'payment_status', 'search']),
            'showArchived'  => $showTrashed,
        ]);
    }


    public function create(): Response
    {
        $shopId = auth()->user()->shop->id;

        return Inertia::render('shop/operations/orders/Create', [
            'shopId'         => $shopId,
            'pickupTypes'    => ShopOrder::PICKUP_TYPES,
            'paymentMethods' => ShopOrder::PAYMENT_METHODS,
            'services'       => ShopServicePricing::where('shop_id', $shopId)
                ->where('is_active', true)
                ->orderBy('service_name')
                ->get(),
            'inventoryItems' => Inventory::where('shop_id', $shopId)
                ->where('status', 'active')
                ->orderBy('name')
                ->get(['id', 'name', 'unit', 'quantity']),
        ]);
    }

    public function store(StoreShopOrderRequest $request): RedirectResponse
    {
        $shopId = auth()->user()->shop->id;

        $order = $this->service->createOrder([
            ...$request->validated(),
            'shop_id' => $shopId,
        ]);

        return redirect('/shop/operations/orders')
            ->with('toast', [
                'type'    => 'success',
                'message' => "Order {$order->order_number} created successfully.",
            ]);
    }

    public function show(ShopOrder $order): Response
    {
        $order->load(['service', 'supplies']);

        return Inertia::render('shop/operations/orders/Show', [
            'shopOrder' => $order,
        ]);
    }

    public function edit(ShopOrder $order): Response
    {
        $shopId = auth()->user()->shop->id;
        $order->load(['service', 'supplies']);

        return Inertia::render('shop/operations/orders/Edit', [
            'shopOrder'          => $order,
            'shopId'         => $shopId,
            'pickupTypes'    => ShopOrder::PICKUP_TYPES,
            'paymentMethods' => ShopOrder::PAYMENT_METHODS,
            'statuses'       => ShopOrder::STATUSES,
            'services'       => ShopServicePricing::where('shop_id', $shopId)
                ->where('is_active', true)
                ->orderBy('service_name')
                ->get(),
            'inventoryItems' => Inventory::where('shop_id', $shopId)
                ->where('status', 'active')
                ->orderBy('name')
                ->get(['id', 'name', 'unit', 'quantity']),
        ]);
    }
    public function update(UpdateShopOrderRequest $request, ShopOrder $order): RedirectResponse
    {
        $this->service->updateOrder($order, $request->validated());

        return redirect('/shop/operations/orders')
            ->with('toast', [
                'type'    => 'success',
                'message' => 'Order updated successfully.',
            ]);
    }

    public function updateStatus(Request $request, int $shopId, ShopOrder $order): RedirectResponse
    {
        $request->validate([
            'status' => ['required', 'in:' . implode(',', ShopOrder::STATUSES)],
        ]);

        $this->service->updateStatus($order, $request->input('status'));

        return back()->with('toast', [
            'type' => 'success',
            'message' => "Order status updated successfully."
        ]);;
    }

    public function updatePayment(Request $request, int $shopId, ShopOrder $order): RedirectResponse
    {
        $request->validate([
            'payment_method' => ['required', 'in:' . implode(',', ShopOrder::PAYMENT_METHODS)],
            'amount_paid'    => ['required', 'numeric', 'min:0'],
        ]);

        $this->service->updatePayment($order, $request->only(['payment_method', 'amount_paid']));

        return back()->with('toast', [
            'type' => 'success',
            'message' => "Order payment updated successfully."
        ]);;
    }

    public function destroy(ShopOrder $order): RedirectResponse
    {
        $this->service->deleteOrder($order);

        return redirect('/shop/operations/orders')
            ->with('toast', [
                'type'    => 'success',
                'message' => 'Order archived successfully.',
            ]);
    }

    public function restore(ShopOrder $order): RedirectResponse
    {
        $order = ShopOrder::withTrashed()->findOrFail($order->id);
        $order->restore();

        return redirect('/shop/operations/orders?archived=true')
            ->with('toast', [
                'type'    => 'success',
                'message' => "Order {$order->order_number} restored successfully.",
            ]);
    }

    public function restoreAll(): RedirectResponse
    {
        $shopId = auth()->user()->shop->id;
        ShopOrder::onlyTrashed()->where('shop_id', $shopId)->restore();

        return redirect('/shop/operations/orders?archived=true')
            ->with('toast', [
                'type'    => 'success',
                'message' => 'All archived orders restored.',
            ]);
    }
}
