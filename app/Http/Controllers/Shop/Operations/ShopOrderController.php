<?php

namespace App\Http\Controllers\Shop\Operations;

use App\Exceptions\DeliveryNotAvailableException;
use App\Http\Controllers\Controller;
use App\Http\Requests\Shop\Operations\CheckoutOrderPaymentRequest;
use App\Http\Requests\Shop\Operations\QuoteCounterDeliveryRequest;
use App\Http\Requests\Shop\Operations\RecordOrderPaymentRequest;
use App\Http\Requests\Shop\Operations\SendOrderForDeliveryRequest;
use App\Http\Requests\Shop\Operations\SetOrderRushRequest;
use App\Http\Requests\Shop\Operations\ShopOrderFilterRequest;
use App\Http\Requests\Shop\Operations\StoreShopOrderRequest;
use App\Http\Requests\Shop\Operations\UpdateOrderStatusRequest;
use App\Http\Requests\Shop\Operations\UpdateShopOrderRequest;
use App\Http\Requests\Shop\Operations\VoidOrderPaymentRequest;
use App\Http\Resources\ShopOrderPaymentResource;
use App\Models\Employee;
use App\Models\Inventory;
use App\Models\Promotion;
use App\Models\ShopOrder;
use App\Models\ShopService;
use App\Services\CounterDeliveryFeeService;
use App\Services\CustomerAgreementService;
use App\Services\LogisticsService;
use App\Services\OrderArchiveService;
use App\Services\OrderCheckoutService;
use App\Services\OrderPaymentService;
use App\Services\OrderReceiptService;
use App\Services\ShopOrderPaymentService;
use App\Services\ShopOrderService;
use App\Services\Sms\ShopSmsService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ShopOrderController extends Controller
{
    public function __construct(
        protected ShopOrderService $service,
        private readonly CustomerAgreementService $customerAgreementService,
        private readonly LogisticsService $logistics,
        private readonly OrderPaymentService $payments,
        private readonly ShopOrderPaymentService $onlinePayments,
        private readonly OrderReceiptService $receipts,
        private readonly OrderArchiveService $archive,
        private readonly OrderCheckoutService $checkout,
        private readonly ShopSmsService $sms,
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
        $filters = $request->filters();

        $orders = $this->service->getPaginatedOrders(
            shopId: $shopId,
            perPage: $filters['per_page'],
            filters: $filters,
            onlyTrashed: $filters['archived'],
        );

        return Inertia::render('shop/operations/orders/Index', [
            'orders' => $orders,
            'statusSummary' => $this->service->getStatusSummary($shopId),
            'newOrdersCount' => $this->service->newOrdersToday($shopId),
            'queueSummary' => $this->service->queueSummary($shopId),
            'rushFee' => $this->service->rushFeeFor($shopId),
            'filters' => $filters,
            'showArchived' => $filters['archived'],
        ]);
    }

    public function create(): Response
    {
        $shopId = $this->getShopId();

        return Inertia::render('shop/operations/orders/Create', [
            'shopId' => $shopId,
            'pickupAwaitsReceipt' => $this->service->pickupOrdersAwaitReceipt($shopId),
            'completionMinutes' => $this->service->defaultCompletionMinutes($shopId),
            'tax' => $this->service->taxFor($shopId),
            'rushFee' => $this->service->rushFeeFor($shopId),
            'riderLoadLimit' => $this->service->riderLoadLimitFor($shopId),
            'deliveryPricing' => app(CounterDeliveryFeeService::class)->pricingFor($shopId),
            'pickupTypes' => ShopOrder::PICKUP_TYPES,
            'paymentMethods' => ShopOrder::PAYMENT_METHODS,
            'services' => ShopService::where('shop_id', $shopId)
                ->where('is_active', true)
                ->orderBy('service_name')
                ->get(),
            'inventoryItems' => Inventory::where('shop_id', $shopId)
                ->where('status', 'active')
                ->usableAsSupply()
                ->orderBy('name')
                ->get(['id', 'name', 'unit', 'quantity', 'selling_price', 'addon_quantity']),
            'promotions' => Promotion::where('shop_id', $shopId)
                ->active()
                ->valid()
                ->orderBy('name')
                ->get(['id', 'name', 'type', 'value', 'min_order_amount']),
            'customerAgreement' => $this->customerAgreementService->agreementDataForShopId($shopId),
        ]);
    }

    /** The delivery fee to the customer's pinned location, shown before the order is saved. */
    public function deliveryQuote(QuoteCounterDeliveryRequest $request, CounterDeliveryFeeService $fees): JsonResponse
    {
        try {
            $quote = $fees->quote($this->getShopId(), (float) $request->validated('lat'), (float) $request->validated('lng'));
        } catch (DeliveryNotAvailableException $exception) {
            return response()->json(['available' => false, 'message' => $exception->getMessage()]);
        }

        return response()->json(['available' => true, ...$quote]);
    }

    public function store(StoreShopOrderRequest $request): RedirectResponse
    {
        $order = $this->service->createOrder(
            [
                ...$request->validated(),
                'shop_id' => $this->getShopId(),
            ],
            $request->user(),
            $request->ip(),
            $request->userAgent(),
        );

        // The order is saved first; payment is taken on the next step, like a checkout.
        return redirect("{$this->base()}/operations/orders/{$order->payment_public_id}/checkout")
            ->with('toast', [
                'type' => 'success',
                'message' => "Order {$order->order_number} created. Take the payment next.",
            ]);
    }

    public function checkout(Request $request, ShopOrder $order): Response
    {
        return Inertia::render('shop/operations/orders/Checkout', [
            ...$this->checkout->pageData($request->user(), $order),
            'base' => "{$this->base()}/operations/orders",
        ]);
    }

    public function confirmCheckout(CheckoutOrderPaymentRequest $request, ShopOrder $order): RedirectResponse
    {
        $payment = $this->checkout->pay($request->user(), $order, $request->validated());
        $walkIn = $this->checkout->opensReceipt($order);
        $destination = $walkIn
            ? "{$this->base()}/operations/orders/{$order->payment_public_id}"
            : "{$this->base()}/operations/orders";

        return redirect($destination)
            ->with('show_receipt', $walkIn)
            ->with('toast', [
                'type' => 'success',
                'message' => $payment
                    ? 'Payment of ₱'.number_format((float) $payment->amount, 2)." recorded for order {$order->order_number}."
                    : "Order {$order->order_number} saved as not paid yet.",
            ]);
    }

    public function show(ShopOrder $order): Response
    {
        $order->load(['service', 'serviceLines', 'supplies', 'shop']);

        return Inertia::render('shop/operations/orders/Show', [
            'shopOrder' => $order,
            'customerAgreement' => $this->customerAgreementService->acceptanceData($order),
            'deliveryOption' => $this->logistics->deliveryOptionFor($order),
            'payments' => ShopOrderPaymentResource::collection($this->payments->history($order))->resolve(),
            'receipt' => $this->receipts->forOrder($order),
            'openReceipt' => (bool) session('show_receipt', false),
            'smsNotice' => $this->sms->orderPanel($order),
            'readyPrompt' => (bool) session('ready_prompt', false),
        ]);
    }

    public function notifyReady(Request $request, ShopOrder $order): RedirectResponse
    {
        $sms = $this->sms->notifyReadyByStaff($request->user(), $order);

        return back()->with('toast', [
            'type' => 'success',
            'message' => "Text sent to {$sms->phone}.",
        ]);
    }

    public function receiptPdf(Request $request, ShopOrder $order): \Illuminate\Http\Response
    {
        // ?inline=1 lets the page print the receipt without downloading it.
        $disposition = $request->boolean('inline') ? 'inline' : 'attachment';

        return response($this->receipts->pdf($order), 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => $disposition.'; filename="receipt-'.$order->order_number.'.pdf"',
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

    public function edit(ShopOrder $order): Response|RedirectResponse
    {
        // A products order has nothing to weigh or change; its actions live on the order page.
        if ($order->isProductOrder()) {
            return redirect("{$this->base()}/operations/orders/{$order->payment_public_id}");
        }

        $shopId = $this->getShopId();
        $order->load(['service', 'serviceLines', 'supplies']);

        return Inertia::render('shop/operations/orders/Edit', [
            'shopOrder' => $order,
            'shopId' => $shopId,
            'weighingBlockedReason' => $this->service->weighingBlockedReason($order),
            'payments' => ShopOrderPaymentResource::collection($this->payments->history($order))->resolve(),
            'pickupTypes' => ShopOrder::PICKUP_TYPES,
            'statuses' => ShopOrder::STATUSES,
            'services' => ShopService::where('shop_id', $shopId)
                ->where('is_active', true)
                ->orderBy('service_name')
                ->get(),
            'inventoryItems' => Inventory::where('shop_id', $shopId)
                ->where('status', 'active')
                ->usableAsSupply($order->supplies->pluck('id')->all())
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

    public function updateStatus(UpdateOrderStatusRequest $request, ShopOrder $order): RedirectResponse
    {
        $this->service->changeStatusByStaff($order, $request->validated('status'));
        $order->refresh();

        $texted = $order->status === 'completed' && $this->sms->shouldAutoNotifyReady($order);

        return back()
            // No text is going out on its own, so the order page asks staff to send one themselves.
            ->with('ready_prompt', $this->sms->needsManualReadyPrompt($order))
            ->with('toast', [
                'type' => 'success',
                'message' => $texted
                    ? "Order completed. {$order->customer_name} is being texted that the laundry is done."
                    : 'Order status updated successfully.',
            ]);
    }

    public function setRush(SetOrderRushRequest $request, ShopOrder $order): RedirectResponse
    {
        $rush = $request->boolean('rush');
        $this->service->setRush($order, $rush, $request->user());

        return back()->with('toast', [
            'type' => 'success',
            'message' => $rush
                ? "Order {$order->order_number} moved to the front of the queue."
                : "Order {$order->order_number} is back in the regular queue.",
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
        $this->archive->archive($order, auth()->id());

        return redirect("{$this->base()}/operations/orders")
            ->with('toast', [
                'type' => 'success',
                'message' => 'Order archived successfully.',
            ]);
    }

    public function restore(string $publicId): RedirectResponse
    {
        $order = $this->archive->restore($publicId, $this->getShopId(), auth()->id());

        return redirect("{$this->base()}/operations/orders?archived=true")
            ->with('toast', [
                'type' => 'success',
                'message' => "Order {$order->order_number} restored successfully.",
            ]);
    }

    public function restoreAll(): RedirectResponse
    {
        $this->archive->restoreAll($this->getShopId(), auth()->id());

        return redirect("{$this->base()}/operations/orders?archived=true")
            ->with('toast', [
                'type' => 'success',
                'message' => 'All archived orders restored.',
            ]);
    }
}
