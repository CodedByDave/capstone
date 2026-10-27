<?php

namespace App\Http\Controllers\User;

use App\Exceptions\DeliveryNotAvailableException;
use App\Exceptions\DeliveryRequestException;
use App\Http\Controllers\Controller;
use App\Http\Requests\User\DeliveryQuoteRequest;
use App\Http\Requests\User\EstimateOrderBreakdownRequest;
use App\Http\Requests\User\RequestDeliveryRequest;
use App\Http\Requests\User\StoreUserOrderRequest;
use App\Models\ShopOrder;
use App\Notifications\OrderNotification;
use App\Services\CustomerAgreementService;
use App\Services\CustomerOrderPlacementService;
use App\Services\DeliveryRequestService;
use App\Services\DeliveryTrackingService;
use App\Services\OrderBillingBreakdownService;
use App\Services\OrderComplaintService;
use App\Services\OrderPaymentService;
use App\Services\OrderRatingService;
use App\Services\ShopOrderPaymentService;
use App\Services\ShopPaymentSettingsService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;

class UserOrderController extends Controller
{
    public function __construct(
        private readonly CustomerOrderPlacementService $orderPlacement,
        private readonly CustomerAgreementService $customerAgreementService,
        private readonly ShopPaymentSettingsService $paymentSettings,
        private readonly OrderBillingBreakdownService $billing,
        private readonly DeliveryRequestService $deliveryRequests,
        private readonly DeliveryTrackingService $deliveryTracking,
        private readonly OrderRatingService $ratings,
        private readonly OrderComplaintService $complaints,
        private readonly OrderPaymentService $orderPayments,
        private readonly ShopOrderPaymentService $onlinePayments,
    ) {}

    // ─── List orders ──────────────────────────────────────────────────────────

    public function index(): Response
    {
        $orders = ShopOrder::where('user_id', auth()->id())
            ->with([
                'shop:id,shop_name,municipality,barangay,phone',
                'service:id,service_name',
            ])
            ->latest()
            ->paginate(15)
            ->through(fn ($o) => [
                'id' => $o->id,
                'payment_public_id' => $o->payment_public_id,
                'order_number' => $o->order_number,
                'shop' => $o->shop ? ['name' => $o->shop->shop_name, 'location' => "{$o->shop->barangay}, {$o->shop->municipality}"] : null,
                'service_name' => $o->service->service_name ?? '—',
                'status' => $o->status,
                'payment_status' => $o->payment_status,
                'total_amount' => (float) $o->total_amount,
                'order_source' => $o->order_source,
                'pickup_type' => $o->pickup_type,
                'created_at' => $o->created_at->toDateString(),
            ]);

        return Inertia::render('user/Orders', [
            'orders' => $orders,
        ]);
    }

    // ─── Show single order ────────────────────────────────────────────────────

    public function show(ShopOrder $order): Response
    {
        abort_if($order->user_id !== auth()->id(), 404);

        $order->load(['shop', 'service', 'supplies', 'delivery.rider', 'delivery.shop', 'delivery.photos']);

        $delivery = $order->delivery;

        return Inertia::render('user/OrderDetail', [
            'customerAgreement' => $this->customerAgreementService->acceptanceData($order),
            'order' => [
                'id' => $order->id,
                'payment_public_id' => $order->payment_public_id,
                'order_number' => $order->order_number,
                'status' => $order->status,
                'payment_status' => $order->payment_status,
                'payment_method' => $order->payment_method,
                'total_amount' => (float) $order->total_amount,
                'amount_paid' => (float) $order->amount_paid,
                'billing' => $this->billing->forOrder($order),
                'balance_due' => $order->balanceDue(),
                ...$this->orderPayments->customerView($order),
                'estimated_weight_kg' => $order->estimated_weight_kg,
                'actual_weight_kg' => $order->actual_weight_kg,
                'pickup_type' => $order->pickup_type,
                'customer_address' => $order->customer_address,
                'special_instructions' => $order->special_instructions,
                'pricing_model' => $order->pricing_model,
                'price_per_kg' => $order->price_per_kg,
                'payment_ready' => $order->priceIsConfirmed(),
                'piece_count' => $order->piece_count,
                'price_per_piece' => $order->price_per_piece,
                'created_at' => $order->created_at->format('M d, Y'),
                'completed_at' => $order->completed_at?->format('M d, Y'),
                'shop_has_paymongo' => $order->shop?->hasPaymongo() ?? false,
                'shop_uses_demo_payment' => $order->shop
                    ? $this->paymentSettings->usesDemoCredentials($order->shop)
                    : false,
                'shop_offers_delivery' => (bool) ($order->shop?->canDeliver() ?? false),
                'shop' => $order->shop ? [
                    'name' => $order->shop->shop_name,
                    'phone' => $order->shop->phone,
                    'municipality' => $order->shop->municipality,
                    'barangay' => $order->shop->barangay,
                    'block_street' => $order->shop->block_street,
                    'latitude' => $order->shop->latitude,
                    'longitude' => $order->shop->longitude,
                ] : null,
                'service_name' => $order->service->service_name ?? '—',
                'delivery' => $delivery ? $this->deliveryTracking->payload($delivery) : null,
                'can_track' => $this->deliveryTracking->hasTrackableLeg($order),
                'rating' => $this->ratings->orderState($order),
                'complaints' => $this->complaints->orderState($order),
            ],
        ]);
    }

    // ─── Estimate before ordering ─────────────────────────────────────────────

    public function estimate(EstimateOrderBreakdownRequest $request): JsonResponse
    {
        $data = $request->validated();

        return response()->json($this->billing->estimateFor(
            (int) $data['shop_id'],
            (int) $data['service_id'],
            (float) ($data['estimated_weight_kg'] ?? 0),
            isset($data['piece_count']) ? (int) $data['piece_count'] : null,
        ));
    }

    // ─── Place order ──────────────────────────────────────────────────────────

    public function store(StoreUserOrderRequest $request): RedirectResponse|SymfonyResponse
    {
        $result = $this->orderPlacement->place(
            $request->user(),
            $request->validated(),
            $request->ip(),
            $request->userAgent(),
        );

        if ($result['checkout_url']) {
            return Inertia::location($result['checkout_url']);
        }

        if ($result['payment_error']) {
            return redirect()->route('user.orders.show', $result['order']->payment_public_id)->with('toast', [
                'type' => 'warning',
                'message' => $result['payment_error'],
            ]);
        }

        return redirect()->route('user.orders.index')->with('toast', [
            'type' => 'success',
            'message' => "Order {$result['order']->order_number} placed! The shop will confirm and process it shortly.",
        ]);
    }

    // ─── Request delivery ─────────────────────────────────────────────────────

    public function requestDelivery(RequestDeliveryRequest $request, ShopOrder $order): RedirectResponse
    {
        try {
            $this->deliveryRequests->request($request->user(), $order->payment_public_id, $request->validated());
        } catch (DeliveryRequestException|DeliveryNotAvailableException $exception) {
            return back()->with('toast', ['type' => 'error', 'message' => $exception->getMessage()]);
        }

        return back()->with('toast', [
            'type' => 'success',
            'message' => 'Delivery requested! The shop will assign a rider shortly.',
        ]);
    }

    public function deliveryQuote(DeliveryQuoteRequest $request, ShopOrder $order): JsonResponse
    {
        $data = $request->validated();

        return response()->json($this->deliveryRequests->quote(
            $request->user(),
            $order->payment_public_id,
            (float) $data['destination_lat'],
            (float) $data['destination_lng'],
        ));
    }

    public function tracking(string $paymentPublicId): JsonResponse
    {
        return response()->json($this->deliveryTracking->forCustomer(auth()->user(), $paymentPublicId));
    }

    // ─── Cancel order ─────────────────────────────────────────────────────────

    public function cancel(ShopOrder $order): RedirectResponse
    {
        abort_if($order->user_id !== auth()->id(), 404);

        if ($order->status !== 'pending') {
            return back()->with('toast', [
                'type' => 'error',
                'message' => 'Only pending orders can be cancelled.',
            ]);
        }

        // Look at any open online checkout first so a payment already made is not lost.
        $this->onlinePayments->cancelPending(auth()->user(), $order->payment_public_id);

        $order->update(['status' => 'cancelled']);

        // Notify shop owner
        $shopOwner = $order->shop?->owner ?? null;
        if ($shopOwner) {
            $shopOwner->notify(new OrderNotification($order, 'cancelled'));
        }

        return redirect()->route('user.orders.index')->with('toast', [
            'type' => 'success',
            'message' => "Order {$order->order_number} has been cancelled.",
        ]);
    }

    // ─── Helpers ──────────────────────────────────────────────────────────────

}
