<?php

namespace App\Http\Controllers;

use App\Services\ShopService;
use Inertia\Inertia;
use Inertia\Response;
use Laravel\Fortify\Features;

class LandingController extends Controller
{
    public function __construct(protected ShopService $shopService) {}

    public function index(): Response
    {
        return Inertia::render('Landing', [
            'canRegister' => Features::enabled(Features::registration()),
            'shopCount' => $this->shopService->countActiveShops(),
        ]);
    }
}
