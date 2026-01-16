<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\RegisterShopRequest;
use App\Services\ShopService;
use Inertia\Inertia;

class ShopRegisterController extends Controller
{
    public function __construct(private ShopService $shopService) {}

    /**
     * Show shop registration page
     */
    public function create()
    {
        return Inertia::render('auth/RegisterShop');
    }

    /**
     * Store shop and owner
     */
    public function store(RegisterShopRequest $request)
    {
        $this->shopService->registerShop($request->validated());

        return redirect()->route('login')
            ->with('toast', [
                'type' => 'success',
                'message' => 'Your account has been created. please proceed to login'
            ]);
    }
}
