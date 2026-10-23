<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Services\DeliveryTrackingService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

/** The customer's live tracking page for the rider collecting or returning their laundry. */
class OrderTrackingController extends Controller
{
    public function __construct(private readonly DeliveryTrackingService $tracking) {}

    public function show(Request $request, string $paymentPublicId): Response
    {
        return Inertia::render('user/OrderTracking', [
            'tracking' => $this->tracking->forOrderPage($request->user(), $paymentPublicId),
        ]);
    }

    public function data(Request $request, string $paymentPublicId): JsonResponse
    {
        return response()->json($this->tracking->forOrderPage($request->user(), $paymentPublicId));
    }
}
