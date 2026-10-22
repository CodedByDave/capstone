<?php

namespace App\Http\Controllers\Shop;

use App\Http\Controllers\Controller;
use App\Http\Requests\Shop\Logistics\StoreRiderRequest;
use App\Http\Requests\Shop\Logistics\UpdateDeliveryStatusRequest;
use App\Http\Requests\Shop\Logistics\UpdateRiderRequest;
use App\Models\Delivery;
use App\Models\Rider;
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

    public function index(Request $request): Response
    {
        return Inertia::render('shop/logistics/Index', [
            ...$this->service->board($request->user(), $request->get('status')),
            'rider_base_url' => rtrim(config('app.rider_base_url') ?: config('app.url'), '/'),
            'filters' => $request->only('status'),
        ]);
    }

    public function updateStatus(UpdateDeliveryStatusRequest $request, Delivery $delivery): RedirectResponse
    {
        $this->service->updateStatus($request->user(), $delivery, $request->validated());

        return back()->with('toast', ['type' => 'success', 'message' => 'Delivery status updated.']);
    }

    public function destroy(Request $request, Delivery $delivery): RedirectResponse
    {
        $this->service->deleteDelivery($request->user(), $delivery);

        return back()->with('toast', ['type' => 'success', 'message' => 'Delivery removed.']);
    }

    // ── Riders ────────────────────────────────────────────────────────────────

    public function riders(Request $request): Response
    {
        return Inertia::render('shop/logistics/Riders', [
            'riders' => $this->service->riderList($request->user()),
        ]);
    }

    public function storeRider(StoreRiderRequest $request): RedirectResponse
    {
        $this->service->createRider($request->user(), $request->validated());

        return back()->with('toast', ['type' => 'success', 'message' => 'Rider added successfully.']);
    }

    public function updateRider(UpdateRiderRequest $request, Rider $rider): RedirectResponse
    {
        $this->service->updateRider($request->user(), $rider, $request->validated());

        return back()->with('toast', ['type' => 'success', 'message' => 'Rider updated.']);
    }

    public function destroyRider(Request $request, Rider $rider): RedirectResponse
    {
        $this->service->deleteRider($request->user(), $rider);

        return back()->with('toast', ['type' => 'success', 'message' => 'Rider removed.']);
    }
}
