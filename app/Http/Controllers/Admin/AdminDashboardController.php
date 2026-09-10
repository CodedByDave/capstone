<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\Admin\AdminDashboardService;
use Inertia\Inertia;
use Inertia\Response;

class AdminDashboardController extends Controller
{
    public function __construct(
        private readonly AdminDashboardService $adminDashboardService,
    ) {}

    public function index(): Response
    {
        return Inertia::render(
            'admin/Dashboard',
            $this->adminDashboardService->getDashboardData(now()),
        );
    }
}
