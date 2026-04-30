<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Delivery;
use App\Models\Shop;
use App\Models\ShopOrder;
use App\Models\ShopService;
use App\Notifications\DeliveryNotification;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use App\Notifications\OrderNotification;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

class UserOrderController extends Controller
{
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
            ->through(fn($o) => [
                'id'             => $o->id,
                'order_number'   => $o->order_number,
                'shop'           => $o->shop ? ['name' => $o->shop->shop_name, 'location' => "{$o->shop->barangay}, {$o->shop->municipality}"] : null,
                'service_name'   => $o->service->service_name ?? '—',
                'status'         => $o->status,
                'payment_status' => $o->payment_status,
                'total_amount'   => (float) $o->total_amount,
                'order_source'   => $o->order_source,
                'pickup_type'    => $o->pickup_type,
                'created_at'     => $o->created_at->toDateString(),
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
            'order' => [
                'id'                   => $order->id,
                'order_number'         => $order->order_number,
                'status'               => $order->status,
                'payment_status'       => $order->payment_status,
                'payment_method'       => $order->payment_method,
                'total_amount'         => (float) $order->total_amount,
                'amount_paid'          => (float) $order->amount_paid,
                'estimated_weight_kg'  => $order->estimated_weight_kg,
                'actual_weight_kg'     => $order->actual_weight_kg,
                'pickup_type'          => $order->pickup_type,
                'customer_address'     => $order->customer_address,
                'special_instructions' => $order->special_instructions,
                'pricing_model'        => $order->pricing_model,
                'price_per_kg'         => $order->price_per_kg,
                'created_at'           => $order->created_at->format('M d, Y'),
                'completed_at'         => $order->completed_at?->format('M d, Y'),
                'shop_has_paymongo'    => $order->shop?->hasPaymongo() ?? false,
                'shop' => $order->shop ? [
                    'name'         => $order->shop->shop_name,
                    'phone'        => $order->shop->phone,
                    'municipality' => $order->shop->municipality,
                    'barangay'     => $order->shop->barangay,
                    'block_street' => $order->shop->block_street,
                ] : null,
                'service_name' => $order->service->service_name ?? '—',
                'delivery' => $delivery ? [
                    'id'               => $delivery->id,
                    'status'           => $delivery->status,
                    'delivery_address' => $delivery->delivery_address,
                    'rider_name'       => $delivery->rider?->name,
                    'picked_up_at'     => $delivery->picked_up_at?->format('M d, Y g:i A'),
                    'delivered_at'     => $delivery->delivered_at?->format('M d, Y g:i A'),
                ] : null,
            ],
        ]);
    }

    // ─── Place order ──────────────────────────────────────────────────────────

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'shop_id'              => 'required|exists:shops,id',
            'service_id'           => 'required|exists:services_and_pricing,id',
            'customer_name'        => 'required|string|max:150',
            'customer_phone'       => 'required|string|max:20',
            'estimated_weight_kg'  => 'required|numeric|min:0.5|max:100',
            'pickup_type'          => 'required|in:walk_in,pickup',
            'payment_method'       => 'required|in:cash,gcash,maya,online',
            'customer_address'     => 'required_if:pickup_type,pickup|nullable|string|max:500',
            'special_instructions' => 'nullable|string|max:500',
        ]);

        $shop = Shop::where('id', $validated['shop_id'])->where('status', 'active')->firstOrFail();

        $service = ShopService::where('id', $validated['service_id'])
            ->where('shop_id', $shop->id)
            ->where('is_active', true)
            ->firstOrFail();

        // Estimate total (actual weight & charges will be set by shop)
        $estimatedTotal = 0;
        if ($service->pricing_model === 'per_kg') {
            $estimatedTotal = round((float) $service->price_per_kg * (float) $validated['estimated_weight_kg'], 2);
        } else {
            $estimatedTotal = (float) $service->bundle_price ?? 0;
        }

        $orderNumber = $this->generateOrderNumber();

        $newOrder = ShopOrder::create([
            'shop_id'              => $shop->id,
            'user_id'              => auth()->id(),
            'order_source'         => 'online',
            'service_id'           => $service->id,
            'order_number'         => $orderNumber,
            'customer_name'        => $validated['customer_name'],
            'customer_phone'       => $validated['customer_phone'],
            'customer_address'     => $validated['customer_address'] ?? null,
            'special_instructions' => $validated['special_instructions'] ?? null,
            'estimated_weight_kg'  => $validated['estimated_weight_kg'],
            'actual_weight_kg'     => null,
            'pickup_type'          => $validated['pickup_type'],
            'pricing_model'        => $service->pricing_model,
            'price_per_kg'         => $service->price_per_kg,
            'bundle_weight_kg'     => $service->bundle_weight_kg,
            'bundle_price'         => $service->bundle_price,
            'additional_charges'   => 0,
            'discount_amount'      => 0,
            'total_amount'         => $estimatedTotal,
            'payment_method'       => $validated['payment_method'],
            'payment_status'       => 'unpaid',
            'amount_paid'          => 0,
            'status'               => 'pending',
        ]);

        $newOrder->load('shop');
        auth()->user()->notify(new OrderNotification($newOrder, 'placed'));

        return redirect()->route('user.orders.index')->with('toast', [
            'type'    => 'success',
            'message' => "Order {$orderNumber} placed! The shop will confirm and process it shortly.",
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
            'shop_id'          => $order->shop_id,
            'shop_order_id'    => $order->id,
            'customer_name'    => $order->customer_name,
            'customer_phone'   => $order->customer_phone,
            'delivery_address' => $data['delivery_address'],
            'status'           => 'pending',
            'notes'            => 'Requested by customer via app.',
        ]);

        // Notify shop owner
        $shopOwner = $order->shop->owner ?? null;
        if ($shopOwner) {
            $shopOwner->notify(new DeliveryNotification($delivery));
        }

        return back()->with('toast', [
            'type'    => 'success',
            'message' => 'Delivery requested! The shop will assign a rider shortly.',
        ]);
    }

    // ─── Cancel order ─────────────────────────────────────────────────────────

    public function cancel(ShopOrder $order): RedirectResponse
    {
        abort_if($order->user_id !== auth()->id(), 403);

        if ($order->status !== 'pending') {
            return back()->with('toast', [
                'type'    => 'error',
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
            'type'    => 'success',
            'message' => "Order {$order->order_number} has been cancelled.",
        ]);
    }

    // ─── Helpers ──────────────────────────────────────────────────────────────

    private function generateOrderNumber(): string
    {
        do {
            $number = 'ONL-' . now()->format('Ymd') . '-' . strtoupper(Str::random(5));
        } while (ShopOrder::where('order_number', $number)->exists());

        return $number;
    }
}
