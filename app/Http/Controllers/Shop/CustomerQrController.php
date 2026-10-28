<?php

namespace App\Http\Controllers\Shop;

use App\Http\Controllers\Controller;
use App\Services\CustomerReferralService;
use App\Services\ShopService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class CustomerQrController extends Controller
{
    public function __construct(
        private readonly CustomerReferralService $referrals,
        private readonly ShopService $shops,
    ) {}

    public function index(Request $request): Response
    {
        $shop = $this->shops->shopForOwner($request->user());

        return Inertia::render('shop/CustomerQr', [
            'shopName' => $shop->shop_name,
            'joinUrl' => $this->referrals->joinUrlFor($shop),
        ]);
    }
}
