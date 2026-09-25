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
        $filters = $request->only(['search', 'status', 'plan', 'date', 'sort_by', 'sort_direction', 'per_page']);
        $perPage = min(max($request->integer('per_page', 20), 5), 100);
        $filters['per_page'] = (string) $perPage;

        return Inertia::render('admin/orders/Index', [
            'orders' => $this->orderService->getPaginated($filters, $perPage),
            'stats' => $this->orderService->getStats(),
            'filters' => $filters,
        ]);
    }

    public function exportCsv(Request $request)
    {
        $orders = $this->orderService->getForCsvExport(
            $request->only(['search', 'status', 'plan', 'date', 'sort_by', 'sort_direction'])
        );

        return response()->streamDownload(function () use ($orders) {
            $output = fopen('php://output', 'w');
            fwrite($output, "\xEF\xBB\xBF");
            fputcsv($output, [
                'transaction_reference', 'owner_email', 'shop_name',
                'owner_name', 'phone', 'block_street', 'municipality',
                'barangay', 'postal_code', 'plan_name', 'billing_months',
                'total_price', 'payment_method', 'status', 'expires_at',
                'is_upgrade', 'is_trial', 'ordered_at',
            ], ',', '"', '');

            foreach ($orders as $order) {
                fputcsv($output, [
                    $order->transaction_reference,
                    $order->user?->email ?? $order->email,
                    $order->shop_name,
                    $order->owner_name,
                    $order->phone,
                    $order->block_street,
                    $order->municipality,
                    $order->barangay,
                    $order->postal_code,
                    $order->plan_name,
                    $order->billing_months,
                    $order->total_price,
                    $order->payment_method,
                    $order->status,
                    $order->expires_at?->toDateTimeString(),
                    $order->is_upgrade ? 'yes' : 'no',
                    $order->is_trial ? 'yes' : 'no',
                    $order->created_at?->toDateTimeString(),
                ], ',', '"', '');
            }

            fclose($output);
        }, 'orders-'.now()->format('Y-m-d-His').'.csv', [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    public function importCsv(Request $request)
    {
        $validated = $request->validate([
            'file' => ['required', 'file', 'mimes:csv,txt', 'max:5120'],
        ]);

        $result = $this->orderService->importCsv($validated['file']);

        return back()->with('toast', [
            'type' => 'success',
            'message' => "CSV import complete: {$result['created']} created and {$result['updated']} updated.",
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
        abort_unless($order->status === 'pending', 409, 'Only pending orders can be approved.');

        $order->update(['status' => 'approved']);

        return redirect()->back()
            ->with('toast', [
                'type' => 'success',
                'message' => "{$order->shop_name} has been approved and can now proceed to payment.",
            ]);
    }

    public function reject(Request $request, Order $order)
    {
        abort_unless($order->status === 'pending', 409, 'Only pending orders can be rejected.');

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
