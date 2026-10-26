<?php

namespace App\Http\Controllers\Shop\Employee;

use App\Http\Controllers\Controller;
use App\Http\Requests\Shop\Employee\ActivityLogRequest;
use App\Models\Employee;
use App\Models\Shop;
use App\Services\ActivityLogService;
use Inertia\Inertia;

class ActivityLogsController extends Controller
{
    public function __construct(
        private readonly ActivityLogService $activityLogService
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

    public function index(ActivityLogRequest $request)
    {
        $shop = $this->getShop();
        $performerIds = null;

        return Inertia::render('shop/employee/logs/Index', [
            'logs' => $this->activityLogService->getPaginatedLogs($shop, $request, $performerIds),
            'performers' => $this->activityLogService->getPerformers($shop, $performerIds),
            'modules' => $this->activityLogService->getModules($shop, $performerIds),
            'filters' => $request->filters(),
        ]);
    }
}
