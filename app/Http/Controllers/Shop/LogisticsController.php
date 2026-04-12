<?php

namespace App\Http\Controllers\Shop;

use App\Http\Controllers\Controller;
use App\Models\Delivery;
use App\Models\Rider;
use App\Models\Shop;
use App\Models\ShopOrder;
use App\Notifications\DeliveryNotification;
use Illuminate\Http\Request;
use Inertia\Inertia;

class LogisticsController extends Controller
{
    private function getShop(): Shop
    {
        return Shop::where('owner_id', auth()->id())->firstOrFail();
    }

    // ── Deliveries ────────────────────────────────────────────────────────────

    public function index(Request $request)
    {
        $shop   = $this->getShop();
        $status = $request->get('status');

        $deliveries = Delivery::with(['rider', 'order:id,order_number'])
            ->where('shop_id', $shop->id)
            ->when($status, fn ($q) => $q->where('status', $status))
            ->latest()
            ->paginate(15)
            ->withQueryString();

        $riders = Rider::where('shop_id', $shop->id)->active()->orderBy('name')->get(['id', 'name', 'vehicle_type']);

        // Orders eligible for delivery: completed orders with no existing delivery
        $pendingOrders = ShopOrder::where('shop_id', $shop->id)
            ->where('status', 'completed')
            ->whereNotIn('id', Delivery::where('shop_id', $shop->id)->whereNotNull('shop_order_id')->pluck('shop_order_id'))
            ->orderByDesc('created_at')
            ->get(['id', 'order_number', 'customer_name', 'customer_phone', 'delivery_address']);

        $stats = [
            'pending'   => Delivery::where('shop_id', $shop->id)->where('status', 'pending')->count(),
            'assigned'  => Delivery::where('shop_id', $shop->id)->where('status', 'assigned')->count(),
            'picked_up' => Delivery::where('shop_id', $shop->id)->where('status', 'picked_up')->count(),
            'delivered' => Delivery::where('shop_id', $shop->id)->where('status', 'delivered')->count(),
            'failed'    => Delivery::where('shop_id', $shop->id)->where('status', 'failed')->count(),
        ];

        return Inertia::render('shop/logistics/Index', [
            'deliveries'    => $deliveries,
            'riders'        => $riders,
            'rider_base_url' => rtrim(env('RIDER_BASE_URL', config('app.url')), '/'),
            'pending_orders' => $pendingOrders,
            'stats'         => $stats,
            'filters'       => $request->only('status'),
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'shop_order_id'    => ['nullable', 'exists:shop_orders,id'],
            'customer_name'    => ['required', 'string', 'max:100'],
            'customer_phone'   => ['nullable', 'string', 'max:20'],
            'delivery_address' => ['nullable', 'string', 'max:500'],
            'rider_id'         => ['nullable', 'exists:riders,id'],
            'notes'            => ['nullable', 'string', 'max:500'],
        ]);

        $shop = $this->getShop();

        Delivery::create([
            'shop_id'          => $shop->id,
            'shop_order_id'    => $data['shop_order_id'] ?? null,
            'customer_name'    => $data['customer_name'],
            'customer_phone'   => $data['customer_phone'] ?? null,
            'delivery_address' => $data['delivery_address'] ?? null,
            'rider_id'         => $data['rider_id'] ?? null,
            'status'           => $data['rider_id'] ? 'assigned' : 'pending',
            'notes'            => $data['notes'] ?? null,
            'assigned_at'      => $data['rider_id'] ? now() : null,
        ]);

        return back()->with('toast', ['type' => 'success', 'message' => 'Delivery created successfully.']);
    }

    public function updateStatus(Request $request, Delivery $delivery)
    {
        $data = $request->validate([
            'status'   => ['required', 'in:pending,assigned,picked_up,delivered,failed'],
            'rider_id' => ['nullable', 'exists:riders,id'],
            'notes'    => ['nullable', 'string', 'max:500'],
        ]);

        $timestamps = [];
        if ($data['status'] === 'assigned' && !$delivery->assigned_at) {
            $timestamps['assigned_at'] = now();
        }
        if ($data['status'] === 'picked_up' && !$delivery->picked_up_at) {
            $timestamps['picked_up_at'] = now();
        }
        if ($data['status'] === 'delivered' && !$delivery->delivered_at) {
            $timestamps['delivered_at'] = now();
        }

        $delivery->update(array_merge([
            'status'   => $data['status'],
            'rider_id' => $data['rider_id'] ?? $delivery->rider_id,
            'notes'    => $data['notes'] ?? $delivery->notes,
        ], $timestamps));

        // Notify the customer linked to the shop order, if any
        $customer = $delivery->order?->customer;
        if ($customer) {
            $delivery->load('rider'); // ensure rider name is fresh
            $customer->notify(new DeliveryNotification($delivery));
        }

        return back()->with('toast', ['type' => 'success', 'message' => 'Delivery status updated.']);
    }

    public function destroy(Delivery $delivery)
    {
        $delivery->delete();
        return back()->with('toast', ['type' => 'success', 'message' => 'Delivery removed.']);
    }

    // ── Riders ────────────────────────────────────────────────────────────────

    public function riders()
    {
        $shop   = $this->getShop();
        $riders = Rider::where('shop_id', $shop->id)->withCount('deliveries')->orderBy('name')->get();

        return Inertia::render('shop/logistics/Riders', [
            'riders' => $riders,
        ]);
    }

    public function storeRider(Request $request)
    {
        $data = $request->validate([
            'name'         => ['required', 'string', 'max:100'],
            'phone'        => ['nullable', 'string', 'max:20'],
            'vehicle_type' => ['nullable', 'string', 'max:50'],
        ]);

        $shop = $this->getShop();
        Rider::create(array_merge($data, ['shop_id' => $shop->id]));

        return back()->with('toast', ['type' => 'success', 'message' => 'Rider added successfully.']);
    }

    public function updateRider(Request $request, Rider $rider)
    {
        $data = $request->validate([
            'name'         => ['required', 'string', 'max:100'],
            'phone'        => ['nullable', 'string', 'max:20'],
            'vehicle_type' => ['nullable', 'string', 'max:50'],
            'status'       => ['required', 'in:active,inactive'],
        ]);

        $rider->update($data);

        return back()->with('toast', ['type' => 'success', 'message' => 'Rider updated.']);
    }

    public function destroyRider(Rider $rider)
    {
        $rider->delete();
        return back()->with('toast', ['type' => 'success', 'message' => 'Rider removed.']);
    }
}
