<?php

namespace App\Http\Controllers\Shop\Operations;

use App\Http\Controllers\Controller;
use App\Http\Requests\Shop\Operations\StoreShopServiceRequest;
use App\Http\Requests\Shop\Operations\UpdateShopServiceRequest;
use App\Models\Employee;
use App\Models\ShopService;
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

    private function getShopId(): int
    {
        $user = auth()->user();
        if ($user->role === 'owner') {
            return $user->shop->id;
        }
        return Employee::where('user_id', $user->id)->value('shop_id');
    }

    private function base(): string
    {
        return auth()->user()->role === 'owner' ? '/shop' : '/staff';
    }

    public function index(Request $request): Response
    {
        $shopId      = $this->getShopId();
        $showTrashed = $request->boolean('archived');

        return Inertia::render('shop/operations/services/Index', [
            'services'      => $this->service->getAll($shopId, onlyTrashed: $showTrashed),
            'pricingModels' => ShopService::PRICING_MODELS,
            'showArchived'  => $showTrashed,
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('shop/operations/services/Create', [
            'pricingModels' => ShopService::PRICING_MODELS,
        ]);
    }

    public function store(StoreShopServiceRequest $request): RedirectResponse
    {
        $this->service->create([
            ...$request->validated(),
            'shop_id' => $this->getShopId(),
        ]);

        return redirect("{$this->base()}/operations/services")
            ->with('toast', [
                'type'    => 'success',
                'message' => 'Service created successfully.',
            ]);
    }

    public function edit(ShopService $service): Response
    {
        return Inertia::render('shop/operations/services/Edit', [
            'service'       => $service,
            'pricingModels' => ShopService::PRICING_MODELS,
        ]);
    }

    public function update(UpdateShopServiceRequest $request, ShopService $service): RedirectResponse
    {
        $this->service->update($service, $request->validated());

        return redirect("{$this->base()}/operations/services")
            ->with('toast', [
                'type'    => 'success',
                'message' => 'Service updated successfully.',
            ]);
    }

    public function destroy(ShopService $service): RedirectResponse
    {
        $this->service->delete($service);

        return redirect("{$this->base()}/operations/services")
            ->with('toast', [
                'type'    => 'success',
                'message' => 'Service archived successfully.',
            ]);
    }

    public function toggleActive(ShopService $service): RedirectResponse
    {
        $updated = $this->service->toggleActive($service);

        return redirect("{$this->base()}/operations/services")
            ->with('toast', [
                'type'    => 'success',
                'message' => $updated->is_active ? 'Service activated.' : 'Service deactivated.',
            ]);
    }

    public function restore(ShopService $service): RedirectResponse
    {
        $service = ShopService::withTrashed()->findOrFail($service->id);
        $this->service->restore($service);

        return redirect("{$this->base()}/operations/services?archived=true")
            ->with('toast', [
                'type'    => 'success',
                'message' => "{$service->service_name} restored successfully.",
            ]);
    }

    public function restoreAll(): RedirectResponse
    {
        $this->service->restoreAll($this->getShopId());

        return redirect("{$this->base()}/operations/services?archived=true")
            ->with('toast', [
                'type'    => 'success',
                'message' => 'All archived services restored.',
            ]);
    }
}
