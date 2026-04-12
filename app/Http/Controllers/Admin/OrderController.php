<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Notifications\PlanRejectedNotification;
use App\Services\OrderService;
use App\Services\PaymongoService;
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
        private readonly OrderService   $orderService,
        private readonly PaymongoService $paymongoService,
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

        return redirect()->back()
            ->with('toast', ['type' => 'success', 'message' => "{$order->shop_name} has been approved. Shop now has dashboard access."]);
    }

    public function reject(Request $request, $id)
    {
        $request->validate([
            'rejection_reason' => ['required', 'string', 'max:1000'],
        ]);

        $order = Order::with(['user', 'payments'])->findOrFail($id);

        $order->update([
            'status'           => 'rejected',
            'rejection_reason' => $request->rejection_reason,
        ]);

        // Attempt an automatic PayMongo refund for each paid payment.
        // GCash / Maya / card refunds are credited back to the original payment method.
        $refundIssued = false;

        foreach ($order->payments->where('status', 'paid') as $payment) {
            $newStatus = 'refund_pending'; // fallback if PayMongo call fails

            if ($payment->paymongo_payment_id) {
                try {
                    $amountCentavos = (int) round($payment->amount * 100);

                    $this->paymongoService->refund(
                        $payment->paymongo_payment_id,
                        $amountCentavos,
                        'others',
                    );

                    $newStatus    = 'refunded';
                    $refundIssued = true;

                    Log::info('PayMongo refund issued', [
                        'order_id'           => $order->id,
                        'payment_id'         => $payment->id,
                        'paymongo_payment_id' => $payment->paymongo_payment_id,
                        'amount'             => $payment->amount,
                    ]);
                } catch (\Exception $e) {
                    // Log and fall through — payment stays refund_pending for manual processing.
                    Log::error('PayMongo auto-refund failed; marked as refund_pending', [
                        'order_id'   => $order->id,
                        'payment_id' => $payment->id,
                        'error'      => $e->getMessage(),
                    ]);
                }
            }

            $payment->update(['status' => $newStatus]);
        }

        // Notify the shop owner via email and in-app notification.
        if ($order->user) {
            $order->user->notify(
                new PlanRejectedNotification($order, $request->rejection_reason, $refundIssued)
            );
        }

        $toastMsg = $refundIssued
            ? "{$order->shop_name} rejected. A PayMongo refund has been issued to the owner."
            : "{$order->shop_name} rejected. Refund could not be processed automatically — please handle it manually.";

        return redirect()->back()
            ->with('toast', ['type' => 'error', 'message' => $toastMsg]);
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
