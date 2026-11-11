<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\OrderFilterRequest;
use App\Http\Requests\Admin\ViewKycDocumentRequest;
use App\Models\Order;
use App\Services\BusinessAgreementService;
use App\Services\KycDocumentVault;
use App\Services\KycReviewService;
use App\Services\OrderService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Inertia\Inertia;
use RuntimeException;
use Symfony\Component\HttpFoundation\HeaderUtils;

class OrderController extends Controller
{
    public function __construct(
        private readonly OrderService $orderService,
        private readonly BusinessAgreementService $businessAgreementService,
        private readonly KycReviewService $kycReviewService,
        private readonly KycDocumentVault $kycVault,
    ) {}

    public function index(OrderFilterRequest $request)
    {
        $filters = $request->filters();

        return Inertia::render('admin/orders/Index', [
            'orders' => $this->orderService->getPaginated($filters, $filters['per_page']),
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

    public function show(Request $request, Order $order)
    {
        return Inertia::render('admin/orders/Show', [
            'order' => $this->orderService->adminDetails($order),
            'kycReview' => $this->kycReviewService->rows($order),
            'kycFlagOptions' => $this->kycReviewService->flagOptions(),
            'agreementAcceptance' => $this->businessAgreementService->acceptanceDataForOrder($order),
        ]);
    }

    /** Opens a KYC document for the Super Admin, decrypted in memory; it is never cached. */
    public function serveKyc(ViewKycDocumentRequest $request): Response
    {
        $path = $request->documentPath();

        try {
            $contents = $this->kycVault->contents($path);
        } catch (RuntimeException $exception) {
            // A missing file is just not found; a file that fails to decrypt is worth knowing about.
            if ($this->kycVault->exists($path)) {
                report($exception);
            }
            abort(404);
        }

        return response($contents, 200, [
            'Content-Type' => $this->kycVault->mimeType($path),
            'Content-Disposition' => HeaderUtils::makeDisposition('inline', $this->kycVault->displayName($path)),
            'Cache-Control' => 'no-store, private',
            'X-Content-Type-Options' => 'nosniff',
        ]);
    }
}
