<?php

namespace App\Http\Controllers\Shop;

use App\Exceptions\ShopSetupException;
use App\Http\Controllers\Controller;
use App\Http\Requests\Shop\StoreShopSetupRequest;
use App\Services\ShopSetupService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ShopSetupController extends Controller
{
    public function __construct(private readonly ShopSetupService $service) {}

    public function show(Request $request): Response|RedirectResponse
    {
        try {
            return Inertia::render('shop/setup/Index', $this->service->pageData($request->user()));
        } catch (ShopSetupException $exception) {
            return redirect()->route('shop.dashboard')->with('toast', [
                'type' => 'error',
                'message' => $exception->getMessage(),
            ]);
        }
    }

    public function store(StoreShopSetupRequest $request): RedirectResponse
    {
        try {
            $this->service->complete($request->user(), $request->validated());

            return redirect()->route('shop.dashboard')->with('toast', [
                'type' => 'success',
                'message' => 'Your shop setup is complete.',
            ]);
        } catch (ShopSetupException $exception) {
            return back()->withErrors(['location_mode' => $exception->getMessage()]);
        }
    }
}
