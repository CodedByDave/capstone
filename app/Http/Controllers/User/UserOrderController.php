<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Http\Requests\User\StoreUserOrderRequest;
use App\Models\Delivery;
use App\Models\ShopOrder;
use App\Notifications\DeliveryNotification;
use App\Notifications\OrderNotification;
use App\Services\CustomerAgreementService;
use App\Services\ShopOrderService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class UserOrderController extends Controller
{
    public function __construct(
        private readonly ShopOrderService $orderService,
        private readonly CustomerAgreementService $customerAgreementService,
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
        abort_if($order->user_id !== auth()->id(), 403);

        $order->load(['shop', 'service', 'delivery.rider']);

        $delivery = $order->delivery;

        return Inertia::render('user/OrderDetail', [
            'customerAgreement' => $this->customerAgreementService->acceptanceData($order),
            'order' => [
                'id' => $order->id,
                'order_number' => $order->order_number,
                'status' => $order->status,
                'payment_status' => $order->payment_status,
                'payment_method' => $order->payment_method,
                'total_amount' => (float) $order->total_amount,
                'amount_paid' => (float) $order->amount_paid,
                'estimated_weight_kg' => $order->estimated_weight_kg,
                'actual_weight_kg' => $order->actual_weight_kg,
                'pickup_type' => $order->pickup_type,
                'customer_address' => $order->customer_address,
                'special_instructions' => $order->special_instructions,
                'pricing_model' => $order->pricing_model,
                'price_per_kg' => $order->price_per_kg,
                'created_at' => $order->created_at->format('M d, Y'),
                'completed_at' => $order->completed_at?->format('M d, Y'),
                'shop_has_paymongo' => $order->shop?->hasPaymongo() ?? false,
                'shop_offers_delivery' => (bool) ($order->shop?->offers_delivery ?? false),
                'shop' => $order->shop ? [
                    'name' => $order->shop->shop_name,
                    'phone' => $order->shop->phone,
                    'municipality' => $order->shop->municipality,
                    'barangay' => $order->shop->barangay,
                    'block_street' => $order->shop->block_street,
                ] : null,
                'service_name' => $order->service->service_name ?? '—',
                'delivery' => $delivery ? [
                    'id' => $delivery->id,
                    'status' => $delivery->status,
                    'delivery_address' => $delivery->delivery_address,
                    'rider_name' => $delivery->rider?->name,
                    'picked_up_at' => $delivery->picked_up_at?->format('M d, Y g:i A'),
                    'delivered_at' => $delivery->delivered_at?->format('M d, Y g:i A'),
                ] : null,
            ],
        ]);
    }

    // ─── Place order ──────────────────────────────────────────────────────────

    public function store(StoreUserOrderRequest $request): RedirectResponse
    {
        $newOrder = $this->orderService->createCustomerOrder(
            $request->user(),
            $request->validated(),
            $request->ip(),
            $request->userAgent(),
        );

        return redirect()->route('user.orders.index')->with('toast', [
            'type' => 'success',
            'message' => "Order {$newOrder->order_number} placed! The shop will confirm and process it shortly.",
        ]);
    }

    // ─── Request delivery ─────────────────────────────────────────────────────

    public function requestDelivery(Request $request, ShopOrder $order): RedirectResponse
    {
        abort_if($order->user_id !== auth()->id(), 403);
        abort_if($order->status !== 'completed' || $order->payment_status !== 'paid', 422);
        abort_if($order->delivery()->exists(), 422);

        $data = $request->validate([
            'delivery_address' => ['required', 'string', 'max:500'],
        ]);

        $delivery = Delivery::create([
            'shop_id' => $order->shop_id,
            'shop_order_id' => $order->id,
            'customer_name' => $order->customer_name,
            'customer_phone' => $order->customer_phone,
            'delivery_address' => $data['delivery_address'],
            'status' => 'pending',
            'notes' => 'Requested by customer via app.',
        ]);

        // Notify shop owner
        $shopOwner = $order->shop->owner ?? null;
        if ($shopOwner) {
            $shopOwner->notify(new DeliveryNotification($delivery));
        }

        return back()->with('toast', [
            'type' => 'success',
            'message' => 'Delivery requested! The shop will assign a rider shortly.',
        ]);
    }

    // ─── Cancel order ─────────────────────────────────────────────────────────

    public function cancel(ShopOrder $order): RedirectResponse
    {
        abort_if($order->user_id !== auth()->id(), 403);

        if ($order->status !== 'pending') {
            return back()->with('toast', [
                'type' => 'error',
                'message' => 'Only pending orders can be cancelled.',
            ]);
        }

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
