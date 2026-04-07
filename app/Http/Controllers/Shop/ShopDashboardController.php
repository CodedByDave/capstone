<?php

namespace App\Http\Controllers\Shop;

use App\Http\Controllers\Controller;
use App\Models\Shop;
use App\Models\Employee;
use App\Services\DashboardService;
use Inertia\Inertia;

class ShopDashboardController extends Controller
{
    public function __construct(
        private readonly DashboardService $dashboardService,
    ) {}

    public function index()
    {
        $user = auth()->user();

        if ($user->role === 'owner') {
            $shop = Shop::where('owner_id', $user->id)->firstOrFail();
        } else {
            $employee = Employee::where('user_id', $user->id)->firstOrFail();
            $shop = Shop::findOrFail($employee->shop_id);
        }

        $kpis = $this->dashboardService->getKPIs($shop);

        return Inertia::render('shop/Dashboard', [
            'kpis'            => $kpis,
            'attendanceTrend' => $this->dashboardService->getAttendanceTrend($shop),
            'payrollTrend'    => $this->dashboardService->getPayrollTrend($shop),
            'performance'     => $this->dashboardService->getEmployeePerformance($shop),
            'insights'        => $this->dashboardService->getInsights($kpis),
            'shop'            => $shop,
        ]);
    }
}
