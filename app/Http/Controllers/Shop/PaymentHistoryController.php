<?php

namespace App\Http\Controllers\Shop;

use App\Http\Controllers\Controller;
use App\Http\Requests\Shop\IndexPaymentHistoryRequest;
use App\Models\Order;
use App\Services\PaymentService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class PaymentHistoryController extends Controller
{
    public function __construct(
        private readonly PaymentService $payments,
    ) {}

    public function index(IndexPaymentHistoryRequest $request): Response
    {
        return Inertia::render('shop/payment/History', $this->payments->historyFor(
            $request->user(),
            $request->validated(),
        ));
    }

    public function show(Request $request, Order $order): Response
    {
        return Inertia::render('shop/payment/PaymentSuccess', [
            'order' => $this->payments->historicalReceipt($request->user(), $order),
            'history' => true,
        ]);
    }
}
