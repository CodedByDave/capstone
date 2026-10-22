<?php

namespace App\Http\Controllers\Shop\Operations;

use App\Http\Controllers\Controller;
use App\Http\Requests\Shop\Operations\RecordOrderPaymentRequest;
use App\Http\Requests\Shop\Operations\SendOrderForDeliveryRequest;
use App\Http\Requests\Shop\Operations\ShopOrderFilterRequest;
use App\Http\Requests\Shop\Operations\StoreShopOrderRequest;
use App\Http\Requests\Shop\Operations\UpdateShopOrderRequest;
use App\Http\Requests\Shop\Operations\VoidOrderPaymentRequest;
use App\Http\Resources\ShopOrderPaymentResource;
use App\Models\Employee;
use App\Models\Inventory;
use App\Models\Promotion;
use App\Models\ShopOrder;
use App\Models\ShopService;
use App\Notifications\OrderNotification;
use App\Services\CustomerAgreementService;
use App\Services\LogisticsService;
use App\Services\OrderPaymentService;
use App\Services\ShopOrderPaymentService;
use App\Services\ShopOrderService;
use App\Traits\BranchScoped;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;

class ShopOrderController extends Controller
{
    use BranchScoped;

    public function __construct(
        protected ShopOrderService $service,
        private readonly CustomerAgreementService $customerAgreementService,
        private readonly LogisticsService $logistics,
        private readonly OrderPaymentService $payments,
        private readonly ShopOrderPaymentService $onlinePayments,
    ) {}

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

    public function index(ShopOrderFilterRequest $request): Response
    {
        $shopId = $this->getShopId();
        $branch = $this->getStaffBranch();
        $filters = $request->filters();

        $orders = $this->service->getPaginatedOrders(
            shopId: $shopId,
            perPage: $filters['per_page'],
            filters: $filters,
            onlyTrashed: $filters['archived'],
            branch: $branch,
        );

        return Inertia::render('shop/operations/orders/Index', [
            'orders' => $orders,
            'statusSummary' => $this->service->getStatusSummary($shopId, $branch),
            'filters' => $filters,
            'showArchived' => $filters['archived'],
        ]);
    }

    public function create(): Response
    {
        $shopId = $this->getShopId();

        return Inertia::render('shop/operations/orders/Create', [
            'shopId' => $shopId,
            'pickupTypes' => ShopOrder::PICKUP_TYPES,
            'paymentMethods' => ShopOrder::PAYMENT_METHODS,
            'services' => ShopService::where('shop_id', $shopId)
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
            'customerAgreement' => $this->customerAgreementService->agreementDataForShopId($shopId),
        ]);
    }

    public function store(StoreShopOrderRequest $request): RedirectResponse
    {
        $order = $this->service->createOrder(
            [
                ...$request->validated(),
                'shop_id' => $this->getShopId(),
                'branch_name' => $this->getStaffBranch(),
            ],
            $request->user(),
            $request->ip(),
            $request->userAgent(),
        );

        return redirect("{$this->base()}/operations/orders")
            ->with('toast', [
                'type' => 'success',
                'message' => "Order {$order->order_number} created successfully.",
            ]);
    }

    public function show(ShopOrder $order): Response
    {
        $order->load(['service', 'supplies', 'shop']);

        $shop = $order->shop;
        $qrUrl = fn (?string $path) => $path ? Storage::disk('public')->url($path) : null;

        return Inertia::render('shop/operations/orders/Show', [
            'shopOrder' => $order,
            'customerAgreement' => $this->customerAgreementService->acceptanceData($order),
            'deliveryOption' => $this->logistics->deliveryOptionFor($order),
            'payments' => ShopOrderPaymentResource::collection($this->payments->history($order))->resolve(),
            'gcash_qr' => $qrUrl($shop->gcash_qr),
            'maya_qr' => $qrUrl($shop->maya_qr),
        ]);
    }

    public function sendForDelivery(SendOrderForDeliveryRequest $request, ShopOrder $order): RedirectResponse
    {
        $delivery = $this->logistics->sendOrderForDelivery($request->user(), $order, $request->validated('delivery_address'));

        return back()->with('toast', [
            'type' => 'success',
            'message' => $delivery->rider_id
                ? "Order {$order->order_number} sent for delivery and a rider was assigned."
                : "Order {$order->order_number} sent for delivery. Assign a rider in Logistics.",
        ]);
    }

    public function sendPaymentRequest(ShopOrder $order): RedirectResponse
    {
        if (! in_array($order->payment_method, ['gcash', 'maya'])) {
            return back()->with('toast', [
                'type' => 'error',
                'message' => 'Payment request can only be sent for GCash or Maya orders.',
            ]);
        }

        $customer = $order->customer;

        if (! $customer) {
            return back()->with('toast', [
                'type' => 'error',
                'message' => 'This order has no linked customer account to notify.',
            ]);
        }

        $shop = $order->shop;
        $qrPath = $order->payment_method === 'gcash' ? $shop->gcash_qr : $shop->maya_qr;
        $qrUrl = $qrPath ? Storage::disk('public')->url($qrPath) : null;

        $customer->notify(new OrderNotification($order, 'payment_request', $qrUrl));

        return back()->with('toast', [
            'type' => 'success',
            'message' => 'Payment request sent to customer.',
        ]);
    }

    public function edit(ShopOrder $order): Response
    {
        $shopId = $this->getShopId();
        $order->load(['service', 'supplies']);

        return Inertia::render('shop/operations/orders/Edit', [
            'shopOrder' => $order,
            'shopId' => $shopId,
            'pickupTypes' => ShopOrder::PICKUP_TYPES,
            'paymentMethods' => ShopOrder::PAYMENT_METHODS,
            'statuses' => ShopOrder::STATUSES,
            'services' => ShopService::where('shop_id', $shopId)
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

    public function update(UpdateShopOrderRequest $request, ShopOrder $order): RedirectResponse
    {
        $this->service->updateOrder($order, $request->validated());

        return redirect("{$this->base()}/operations/orders")
            ->with('toast', [
                'type' => 'success',
                'message' => 'Order updated successfully.',
            ]);
    }

    public function updateStatus(Request $request, ShopOrder $order): RedirectResponse
    {
        $request->validate([
            'status' => ['required', 'in:'.implode(',', ShopOrder::STATUSES)],
        ]);

        $this->service->updateStatus($order, $request->input('status'));

        return back()->with('toast', [
            'type' => 'success',
            'message' => 'Order status updated successfully.',
        ]);
    }

    public function recordPayment(RecordOrderPaymentRequest $request, ShopOrder $order): RedirectResponse
    {
        $payment = $this->payments->recordShopPayment($request->user(), $order, $request->validated());
        $balance = $order->fresh()->balanceDue();

        return back()->with('toast', [
            'type' => 'success',
            'message' => 'Payment of ₱'.number_format((float) $payment->amount, 2).' recorded. '
                .($balance > 0 ? 'Remaining balance: ₱'.number_format($balance, 2).'.' : 'The order is fully paid.'),
        ]);
    }

    public function voidPayment(VoidOrderPaymentRequest $request, ShopOrder $order, string $payment): RedirectResponse
    {
        $this->payments->voidPayment($request->user(), $order, $payment, $request->validated('reason'));

        return back()->with('toast', [
            'type' => 'success',
            'message' => 'Payment voided. The balance has been restored.',
        ]);
    }

    public function checkPayments(Request $request, ShopOrder $order): RedirectResponse
    {
        $result = $this->onlinePayments->reconcileForShop($request->user(), $order);

        return back()->with('toast', [
            'type' => $result === 'confirmed' ? 'success' : 'info',
            'message' => match ($result) {
                'confirmed' => 'The online payment was confirmed.',
                'pending' => 'The online payment has not been completed yet.',
                default => 'There is no online payment waiting to be confirmed.',
            },
        ]);
    }

    public function destroy(ShopOrder $order): RedirectResponse
    {
        $this->service->deleteOrder($order);

        return redirect("{$this->base()}/operations/orders")
            ->with('toast', [
                'type' => 'success',
                'message' => 'Order archived successfully.',
            ]);
    }

    public function restore(ShopOrder $order): RedirectResponse
    {
        $order = ShopOrder::withTrashed()->findOrFail($order->id);
        $order->restore();

        return redirect("{$this->base()}/operations/orders?archived=true")
            ->with('toast', [
                'type' => 'success',
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
                'type' => 'success',
                'message' => 'All archived orders restored.',
            ]);
    }
}
