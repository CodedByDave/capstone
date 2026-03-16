<?php

namespace App\Http\Controllers\Shop\Employee;

use App\Http\Controllers\Controller;
use App\Http\Requests\Shop\ActivityLogRequest;
use App\Services\ActivityLogService;
use App\Models\Shop;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ActivityLogsController extends Controller
{
    public function __construct(
        private readonly ActivityLogService $activityLogService
    ){}

    private function getShop(): Shop
    {
        return Shop::where('owner_id', auth()->id())->firstOrFail();
    }

    // Index
    public function index(ActivityLogRequest $request)
    {
        $shop = $this->getShop();

        return Inertia::render('shop/employee/logs/Index', [
            'logs'       => $this->activityLogService->getPaginatedLogs($shop, $request),
            'performers' => $this->activityLogService->getPerformers($shop),
            'modules'    => $this->activityLogService->getModules($shop),
            'filters'    => $request->filters(),
        ]);
    }
}
