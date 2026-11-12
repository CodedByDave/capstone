<?php

namespace App\Http\Controllers\Shop;

use App\Http\Controllers\Controller;
use App\Http\Requests\Shop\Logistics\MarkDroppedOffRequest;
use App\Http\Requests\Shop\Logistics\UpdateDeliveryStatusRequest;
use App\Http\Requests\TableSortRequest;
use App\Models\Delivery;
use App\Services\LogisticsService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Serves both /shop/logistics (owner) and /staff/logistics (staff with the
 * Logistics permission); the route group decides who may reach each action.
 */
class LogisticsController extends Controller
{
    public function __construct(private readonly LogisticsService $service) {}

    // ── Deliveries ────────────────────────────────────────────────────────────

    public function index(TableSortRequest $request): Response
    {
        $showArchived = $request->boolean('archived');

        return Inertia::render('shop/logistics/Index', [
            ...$this->service->board($request->user(), $request->get('status'), $request->sort(), $showArchived),
            'filters' => $request->only('status'),
            'showArchived' => $showArchived,
        ]);
    }

    public function updateStatus(UpdateDeliveryStatusRequest $request, Delivery $delivery): RedirectResponse
    {
        $this->service->updateStatus($request->user(), $delivery, $request->validated());

        return back()->with('toast', ['type' => 'success', 'message' => 'Delivery status updated.']);
    }

    public function markDroppedOff(MarkDroppedOffRequest $request, Delivery $delivery): RedirectResponse
    {
        $this->service->markDroppedOff($request->user(), $delivery);

        return back()->with('toast', ['type' => 'success', 'message' => 'Laundry marked as received at the shop.']);
    }

    public function destroy(Request $request, Delivery $delivery): RedirectResponse
    {
        $this->service->deleteDelivery($request->user(), $delivery);

        return back()->with('toast', ['type' => 'success', 'message' => 'Delivery archived. You can restore it from the archive.']);
    }

    public function restore(Request $request, string $publicId): RedirectResponse
    {
        $this->service->restoreDelivery($request->user(), $publicId);

        return back()->with('toast', ['type' => 'success', 'message' => 'Delivery restored.']);
    }
}
