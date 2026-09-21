<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Notifications\PlanRejectedNotification;
use App\Services\OrderService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Symfony\Component\HttpFoundation\StreamedResponse;

class OrderController extends Controller
{
    public function __construct(
        private readonly OrderService $orderService,
    ) {}

    public function index(Request $request)
    {
        $filters = $request->only(['search', 'status', 'plan', 'date']);

        return Inertia::render('admin/orders/Index', [
            'orders' => $this->orderService->getPaginated($filters),
            'stats' => $this->orderService->getStats(),
            'filters' => $filters,
        ]);
    }

    public function show(Order $order)
    {
        return Inertia::render('admin/orders/Show', [
            'order' => $order->load(['user', 'modules', 'payments']),
        ]);
    }

    public function approve(Order $order)
    {
        $order->update(['status' => 'approved']);

        // If this is a plan upgrade, expire all previous active orders for this user
        if ($order->is_upgrade) {
            Order::where('user_id', $order->user_id)
                ->whereIn('status', ['approved', 'paid'])
                ->where('id', '!=', $order->id)
                ->update(['status' => 'expired']);
        }

        $this->orderService->syncApprovedShop($order);

        $message = $order->is_upgrade
            ? "{$order->shop_name} plan upgraded to {$order->plan_name}. Previous plan expired."
            : "{$order->shop_name} has been approved. Shop now has dashboard access.";

        return redirect()->back()
            ->with('toast', ['type' => 'success', 'message' => $message]);
    }

    public function reject(Request $request, Order $order)
    {
        $request->validate([
            'rejection_reason' => ['required', 'string', 'max:1000'],
        ]);

        $order->load('user');

        $order->update([
            'status' => 'rejected',
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

        if (! $path || ! Storage::disk('private')->exists($path)) {
            abort(404);
        }

        return Storage::disk('private')->response($path);
    }
}
