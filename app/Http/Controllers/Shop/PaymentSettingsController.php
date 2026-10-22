<?php

namespace App\Http\Controllers\Shop;

use App\Http\Controllers\Controller;
use App\Services\ShopPaymentSettingsService;
use Inertia\Inertia;
use Inertia\Response;

/** Online payments run through PayMongo only; this page connects the shop's PayMongo account. */
class PaymentSettingsController extends Controller
{
    public function index(ShopPaymentSettingsService $paymentSettings): Response
    {
        return Inertia::render('shop/PaymentSettings', $paymentSettings->pageData(request()->user()));
    }
}
