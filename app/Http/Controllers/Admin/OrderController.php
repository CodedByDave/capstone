<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Notifications\PlanRejectedNotification;
use App\Services\OrderService;
use App\Models\Order;
use App\Models\Shop;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Inertia\Inertia;

class OrderController extends Controller
{
    public function __construct(
        private readonly OrderService $orderService,
    ) {}

    public function index(Request $request)
    {
        $filters = $request->only(['search', 'status', 'plan', 'date']);

        return Inertia::render('admin/orders/Index', [
            'orders'  => $this->orderService->getPaginated($filters),
            'stats'   => $this->orderService->getStats(),
            'filters' => $filters,
        ]);
    }

    public function show(int $id)
    {
        return Inertia::render('admin/orders/Show', [
            'order' => $this->orderService->find($id),
        ]);
    }

    public function approve($id)
    {
        $order = Order::findOrFail($id);
        $order->update(['status' => 'approved']);

        // If this is a plan upgrade, expire all previous active orders for this user
        if ($order->is_upgrade) {
            Order::where('user_id', $order->user_id)
                ->whereIn('status', ['approved', 'paid'])
                ->where('id', '!=', $order->id)
                ->update(['status' => 'expired']);
        }

        // Create the shop record if it doesn't exist yet, then activate it.
        // The Order holds all shop details submitted during checkout.
        Shop::withTrashed()->updateOrCreate(
            ['owner_id' => $order->user_id],
            [
                'shop_name'    => $order->shop_name,
                'phone'        => $order->phone       ?? null,
                'block_street' => $order->block_street ?? null,
                'municipality' => $order->municipality ?? '',
                'barangay'     => $order->barangay     ?? '',
                'postal_code'  => $order->postal_code  ?? null,
                'status'       => 'active',
                'deleted_at'   => null,   // restore if soft-deleted
            ]
        );

        $message = $order->is_upgrade
            ? "{$order->shop_name} plan upgraded to {$order->plan_name}. Previous plan expired."
            : "{$order->shop_name} has been approved. Shop now has dashboard access.";

        return redirect()->back()
            ->with('toast', ['type' => 'success', 'message' => $message]);
    }

    public function reject(Request $request, $id)
    {
        $request->validate([
            'rejection_reason' => ['required', 'string', 'max:1000'],
        ]);

        $order = Order::with('user')->findOrFail($id);

        $order->update([
            'status'           => 'rejected',
            'rejection_reason' => $request->rejection_reason,
        ]);

        // Notify the shop owner via email and in-app notification.
        if ($order->user) {
            $order->user->notify(
                new PlanRejectedNotification($order, $request->rejection_reason, false)
            );
        }

        return redirect()->back()
            ->with('toast', ['type' => 'error', 'message' => "{$order->shop_name} rejected. No payment was collected."]);
    }

    public function serveKyc(Request $request): StreamedResponse
    {
        $path = $request->query('path');

        if (!$path || !Storage::disk('private')->exists($path)) {
            abort(404);
        }

        return Storage::disk('private')->response($path);
    }
}
