<?php

namespace App\Http\Controllers\Shop;

use App\Http\Controllers\Controller;
use App\Http\Requests\Shop\StoreOrderRequest;
use App\Models\Order;
use App\Models\Payment;
use App\Services\OrderService;
use App\Services\PaymentService;
use App\Services\StripeService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;

class CheckoutController extends Controller
{
    public function __construct(
        protected OrderService $orderService,
        protected PaymentService $paymentService,
        protected StripeService $stripeService
    ) {}

    public function checkout(StoreOrderRequest $request)
    {
        try {
            Log::info('=== CHECKOUT START ===', $request->all());

            $user = auth()->user();

            $order = $this->orderService->create([
                ...$request->validated(),
                'user_id' => $user->id,
            ]);

            Log::info('Order created', [
                'order_id' => $order->id,
                'total_price' => $order->total_price,
                'modules' => $order->modules
            ]);

            $payment = $this->paymentService->createForOrder($order, [
                'payment_method' => 'stripe',
                'amount' => $order->total_price,
            ]);

            Log::info('Payment record created', ['payment_id' => $payment->id]);

            $session = $this->stripeService->createCheckoutSession($order);

            Log::info('Checkout session created successfully', [
                'session_id' => $session['data']['id'],
                'checkout_url' => $session['data']['attributes']['checkout_url']
            ]);

            return response()->json([
                'success' => true,
                'checkout_url' => $session['data']['attributes']['checkout_url']
            ]);

        } catch (\Exception $e) {
            Log::error('=== CHECKOUT FAILED ===', [
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 422);
        }
    }

    public function success(Request $request)
    {
        $orderId = $request->query('order_id');

        if ($orderId) {
            $order = Order::find($orderId);
            $payment = Payment::where('order_id', $orderId)->first();

            // Update order status to paid
            if ($order) {
                $order->update(['status' => 'paid']);
            }

            // Update payment status
            if ($payment) {
                $payment->update([
                    'status' => 'paid',
                    'paid_at' => now()
                ]);
            }

            return Inertia::render('shop/payment/PaymentSuccess', [
                'order' => $order,
                'payment' => $payment
            ]);
        }

        // If no order_id, still show success page but without data
        return Inertia::render('shop/payment/PaymentSuccess');
    }

    public function cancel()
    {
        return Inertia::render('shop/payment/PaymentCancel');
    }
}
