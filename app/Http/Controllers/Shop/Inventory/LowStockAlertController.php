<?php

namespace App\Http\Controllers\Shop\Inventory;

use App\Http\Controllers\Controller;
use App\Http\Requests\Shop\Inventory\UpdateLowStockAlertRequest;
use App\Models\LowStockAlert;
use App\Models\Shop;
use App\Models\Employee;
use App\Services\LowStockAlertService;
use Inertia\Inertia;

class LowStockAlertController extends Controller
{
    public function __construct(
        private readonly LowStockAlertService $alertService,
    ) {}

    private function getShop(): Shop
    {
        $user = auth()->user();

        if ($user->role === 'owner') {
            return Shop::where('owner_id', $user->id)->firstOrFail();
        }

        $employee = Employee::where('user_id', $user->id)->firstOrFail();
        return Shop::findOrFail($employee->shop_id);
    }

    public function index()
    {
        $shop = $this->getShop();

        return Inertia::render('shop/inventory/alert/Index', [
            'alerts'      => $this->alertService->paginate($shop->id),
            'unreadCount' => $this->alertService->unreadCount($shop->id),
        ]);
    }

    public function update(UpdateLowStockAlertRequest $request, LowStockAlert $lowStockAlert)
    {
        match ($request->validated('status')) {
            'read'      => $this->alertService->markAsRead($lowStockAlert),
            'resolved'  => $this->alertService->resolve($lowStockAlert),
            'dismissed' => $this->alertService->dismiss($lowStockAlert),
        };

        return back()->with('toast', ['type' => 'success', 'message' => 'Alert updated.']);
    }

    public function markAllRead()
    {
        $shop = $this->getShop();

        $this->alertService->markAllReadForShop($shop->id);

        return back()->with('toast', ['type' => 'success', 'message' => 'All alerts marked as read.']);
    }
}
