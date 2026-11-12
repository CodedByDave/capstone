<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Http\Requests\User\EstimateProductOrderRequest;
use App\Http\Requests\User\StoreProductOrderRequest;
use App\Services\CustomerProductOrderService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;

/** A customer buying a shop's retail products on their own, to collect at the shop. */
class UserProductOrderController extends Controller
{
    public function __construct(private readonly CustomerProductOrderService $productOrders) {}

    public function estimate(EstimateProductOrderRequest $request): JsonResponse
    {
        $data = $request->validated();

        return response()->json($this->productOrders->estimate((int) $data['shop_id'], $data['retail_items']));
    }

    public function store(StoreProductOrderRequest $request): RedirectResponse
    {
        $order = $this->productOrders->place($request->user(), $request->validated());

        return redirect()->route('user.orders.show', $order->payment_public_id)->with('toast', [
            'type' => 'success',
            'message' => "Order {$order->order_number} placed! You'll be notified when your products are ready for pickup.",
        ]);
    }
}
