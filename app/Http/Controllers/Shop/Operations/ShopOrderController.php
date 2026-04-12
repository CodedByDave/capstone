<?php

namespace App\Http\Controllers\Shop\Operations;

use App\Http\Controllers\Controller;
use App\Http\Requests\Shop\Operations\StoreShopOrderRequest;
use App\Http\Requests\Shop\Operations\UpdateShopOrderRequest;
use App\Models\Employee;
use App\Models\Promotion;
use App\Models\ShopOrder;
use App\Models\ShopService;
use App\Models\Inventory;
use App\Services\ShopOrderService;
use App\Traits\BranchScoped;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ShopOrderController extends Controller
{
    use BranchScoped;

    public function __construct(protected ShopOrderService $service) {}

    private function getShopId(): int
    {
        $user = auth()->user();
        if ($user->role === 'owner') {
            return $user->shop->id;
        }
        return Employee::where('user_id', $user->id)->value('shop_id');
    }

    private function base(): string
    {
        return auth()->user()->role === 'owner' ? '/shop' : '/staff';
    }

    public function index(Request $request): Response
    {
        $shopId      = $this->getShopId();
        $branch      = $this->getStaffBranch();
        $showTrashed = $request->boolean('archived');

        $orders = $this->service->getPaginatedOrders(
            shopId: $shopId,
            perPage: 15,
            filters: $request->only(['status', 'payment_status', 'search']),
            onlyTrashed: $showTrashed,
            branch: $branch,
        );

        return Inertia::render('shop/operations/orders/Index', [
            'orders'        => $orders,
            'statusSummary' => $this->service->getStatusSummary($shopId, $branch),
            'filters'       => $request->only(['status', 'payment_status', 'search']),
            'showArchived'  => $showTrashed,
        ]);
    }

    public function create(): Response
    {
        $shopId = $this->getShopId();

        return Inertia::render('shop/operations/orders/Create', [
            'shopId'         => $shopId,
            'pickupTypes'    => ShopOrder::PICKUP_TYPES,
            'paymentMethods' => ShopOrder::PAYMENT_METHODS,
            'services'       => ShopService::where('shop_id', $shopId)
                ->where('is_active', true)
                ->orderBy('service_name')
                ->get(),
            'inventoryItems' => Inventory::where('shop_id', $shopId)
                ->where('status', 'active')
                ->orderBy('name')
                ->get(['id', 'name', 'unit', 'quantity', 'selling_price']),
            'promotions' => Promotion::where('shop_id', $shopId)
                ->active()
                ->valid()
                ->orderBy('name')
                ->get(['id', 'name', 'type', 'value', 'min_order_amount']),
        ]);
    }

    public function store(StoreShopOrderRequest $request): RedirectResponse
    {
        $order = $this->service->createOrder([
            ...$request->validated(),
            'shop_id'     => $this->getShopId(),
            'branch_name' => $this->getStaffBranch(),
        ]);

        return redirect("{$this->base()}/operations/orders")
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
        $shopId = $this->getShopId();
        $order->load(['service', 'supplies']);

        return Inertia::render('shop/operations/orders/Edit', [
            'shopOrder'      => $order,
            'shopId'         => $shopId,
            'pickupTypes'    => ShopOrder::PICKUP_TYPES,
            'paymentMethods' => ShopOrder::PAYMENT_METHODS,
            'statuses'       => ShopOrder::STATUSES,
            'services'       => ShopService::where('shop_id', $shopId)
                ->where('is_active', true)
                ->orderBy('service_name')
                ->get(),
            'inventoryItems' => Inventory::where('shop_id', $shopId)
                ->where('status', 'active')
                ->orderBy('name')
                ->get(['id', 'name', 'unit', 'quantity', 'selling_price']),
        ]);
    }

    public function update(UpdateShopOrderRequest $request, ShopOrder $order): RedirectResponse
    {
        $this->service->updateOrder($order, $request->validated());

        return redirect("{$this->base()}/operations/orders")
            ->with('toast', [
                'type'    => 'success',
                'message' => 'Order updated successfully.',
            ]);
    }

    public function updateStatus(Request $request, ShopOrder $order): RedirectResponse
    {
        $request->validate([
            'status' => ['required', 'in:' . implode(',', ShopOrder::STATUSES)],
        ]);

        $this->service->updateStatus($order, $request->input('status'));

        return back()->with('toast', [
            'type'    => 'success',
            'message' => 'Order status updated successfully.',
        ]);
    }

    public function updatePayment(Request $request, ShopOrder $order): RedirectResponse
    {
        $request->validate([
            'payment_method' => ['required', 'in:' . implode(',', ShopOrder::PAYMENT_METHODS)],
            'amount_paid'    => ['required', 'numeric', 'min:0'],
        ]);

        $this->service->updatePayment($order, $request->only(['payment_method', 'amount_paid']));

        return back()->with('toast', [
            'type'    => 'success',
            'message' => 'Order payment updated successfully.',
        ]);
    }

    public function destroy(ShopOrder $order): RedirectResponse
    {
        $this->service->deleteOrder($order);

        return redirect("{$this->base()}/operations/orders")
            ->with('toast', [
                'type'    => 'success',
                'message' => 'Order archived successfully.',
            ]);
    }

    public function restore(ShopOrder $order): RedirectResponse
    {
        $order = ShopOrder::withTrashed()->findOrFail($order->id);
        $order->restore();

        return redirect("{$this->base()}/operations/orders?archived=true")
            ->with('toast', [
                'type'    => 'success',
                'message' => "Order {$order->order_number} restored successfully.",
            ]);
    }

    public function restoreAll(): RedirectResponse
    {
        $query = ShopOrder::onlyTrashed()->where('shop_id', $this->getShopId());
        $this->scopeBranch($query);
        $query->restore();

        return redirect("{$this->base()}/operations/orders?archived=true")
            ->with('toast', [
                'type'    => 'success',
                'message' => 'All archived orders restored.',
            ]);
    }
}
