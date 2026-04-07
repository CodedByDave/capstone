<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\OrderService;
use App\Models\Order;
use App\Models\Shop;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
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

        Shop::where('owner_id', $order->user_id)
            ->update(['status' => 'active']);

        return redirect()->back()
            ->with('toast', ['type' => 'success', 'message' => "{$order->shop_name} has been approved. Shop now has dashboard access."]);
    }

    public function reject($id)
    {
        $order = Order::findOrFail($id);
        $order->update(['status' => 'rejected']);

        return redirect()->back()
            ->with('toast', ['type' => 'error', 'message' => "{$order->shop_name} has been rejected."]);
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
