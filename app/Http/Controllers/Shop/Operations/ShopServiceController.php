<?php

namespace App\Http\Controllers\Shop\Operations;

use App\Http\Controllers\Controller;
use App\Http\Requests\Shop\Operations\StoreShopServiceRequest;
use App\Http\Requests\Shop\Operations\UpdateShopServiceRequest;
use App\Models\ShopServicePricing;
use App\Services\ShopServiceService;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;
use Illuminate\Http\Request;

class ShopServiceController extends Controller
{
    public function __construct(
        protected ShopServiceService $service
    ) {}

    public function index(Request $request): Response
    {
        $shopId      = auth()->user()->shop->id;
        $showTrashed = $request->boolean('archived');

        return Inertia::render('shop/operations/services/Index', [
            'services'      => $this->service->getAll($shopId, onlyTrashed: $showTrashed),
            'pricingModels' => ShopServicePricing::PRICING_MODELS,
            'showArchived'  => $showTrashed,
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('shop/operations/services/Create', [
            'pricingModels' => ShopServicePricing::PRICING_MODELS,
        ]);
    }

    public function store(StoreShopServiceRequest $request): RedirectResponse
    {
        $shopId = auth()->user()->shop->id;

        $this->service->create([
            ...$request->validated(),
            'shop_id' => $shopId,
        ]);

        return redirect('/shop/operations/services')
            ->with('toast', [
                'type'    => 'success',
                'message' => 'Service created successfully.',
            ]);
    }

    public function edit(ShopServicePricing $service): Response
    {
        return Inertia::render('shop/operations/services/Edit', [
            'service'       => $service,
            'pricingModels' => ShopServicePricing::PRICING_MODELS,
        ]);
    }

    public function update(UpdateShopServiceRequest $request, ShopServicePricing $service): RedirectResponse
    {
        $this->service->update($service, $request->validated());

        return redirect('/shop/operations/services')
            ->with('toast', [
                'type'    => 'success',
                'message' => 'Service updated successfully.',
            ]);
    }

    public function destroy(ShopServicePricing $service): RedirectResponse
    {
        $this->service->delete($service);

        return redirect('/shop/operations/services')
            ->with('toast', [
                'type'    => 'success',
                'message' => 'Service archived successfully.',
            ]);
    }

    public function toggleActive(ShopServicePricing $service): RedirectResponse
    {
        $updated = $this->service->toggleActive($service);

        return redirect('/shop/operations/services')
            ->with('toast', [
                'type'    => 'success',
                'message' => $updated->is_active ? 'Service activated.' : 'Service deactivated.',
            ]);
    }

    public function restore(ShopServicePricing $service): RedirectResponse
    {
        $service = ShopServicePricing::withTrashed()->findOrFail($service->id);
        $this->service->restore($service);

        return redirect('/shop/operations/services?archived=true')
            ->with('toast', [
                'type'    => 'success',
                'message' => "{$service->service_name} restored successfully.",
            ]);
    }

    public function restoreAll(): RedirectResponse
    {
        $shopId = auth()->user()->shop->id;
        $this->service->restoreAll($shopId);

        return redirect('/shop/operations/services?archived=true')
            ->with('toast', [
                'type'    => 'success',
                'message' => 'All archived services restored.',
            ]);
    }
}