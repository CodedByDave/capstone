<?php

namespace App\Http\Controllers\Shop;

use App\Http\Controllers\Controller;
use App\Http\Requests\Shop\StoreOrderRequest;
use App\Models\Order;
use App\Models\Payment;
use App\Models\Shop;
use App\Services\OrderService;
use App\Services\PaymentService;
use App\Services\PaymongoService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;
use Inertia\Response;

class CheckoutController extends Controller
{
    public function __construct(
        protected OrderService    $orderService,
        protected PaymentService  $paymentService,
        protected PaymongoService $paymongoService
    ) {}

    public function checkout(StoreOrderRequest $request): JsonResponse
    {
        try {
            Log::info('=== CHECKOUT START ===', $request->all());

            $user = auth()->user();

            [$order, $payment] = DB::transaction(function () use ($request, $user) {
                $order = $this->orderService->create([
                    ...$request->validated(),
                    'user_id' => $user->id,
                ]);

                Log::info('Order created', [
                    'order_id'    => $order->id,
                    'total_price' => $order->total_price,
                    'modules'     => $order->modules,
                ]);

                $payment = $this->paymentService->createForOrder($order, [
                    'payment_method' => 'paymongo',
                    'amount'         => $order->total_price,
                ]);

                Log::info('Payment record created', ['payment_id' => $payment->id]);

                return [$order, $payment];
            });

            $session = $this->paymongoService->createCheckoutSession($order);

            // Save the PayMongo session ID for later verification
            Payment::where('order_id', $order->id)->update([
                'paymongo_session_id' => $session['data']['id'],
            ]);

            Log::info('Checkout session created successfully', [
                'session_id'          => $session['data']['id'],
                'checkout_url'        => $session['data']['attributes']['checkout_url'],
                'paymongo_session_id' => Payment::where('order_id', $order->id)->value('paymongo_session_id'),
            ]);

            return response()->json([
                'success'      => true,
                'checkout_url' => $session['data']['attributes']['checkout_url'],
            ]);
        } catch (\Exception $e) {
            Log::error('=== CHECKOUT FAILED ===', [
                'error' => $e->getMessage(),
                'file'  => $e->getFile(),
                'line'  => $e->getLine(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'error'   => $e->getMessage(),
            ], 422);
        }
    }

    public function success(Request $request): Response
    {
        $orderId = $request->query('order_id');

        if ($orderId) {
            $order   = Order::with('modules')->find($orderId);
            $payment = Payment::where('order_id', $orderId)->first();

            if ($payment && $payment->paymongo_session_id) {
                try {
                    $session       = $this->paymongoService->getCheckoutSession($payment->paymongo_session_id);
                    $sessionStatus = $session['data']['attributes']['status'] ?? null;
                    $paymentStatus = $session['data']['attributes']['payment_intent']['attributes']['status'] ?? null;

                    if (
                        in_array($sessionStatus, ['completed', 'paid']) ||
                        in_array($paymentStatus, ['paid', 'succeeded'])
                    ) {
                        $payment->update([
                            'status'  => 'paid',
                            'paid_at' => now(),
                        ]);

                        $order?->update(['status' => 'paid']);

                        // Activate the shop once payment is confirmed
                        Shop::where('owner_id', $order->user_id)
                            ->update(['status' => 'active']);

                        Log::info('Shop activated after payment', [
                            'order_id' => $orderId,
                            'user_id'  => $order->user_id,
                        ]);

                        $payment = $payment->fresh();
                        $order   = $order->fresh()->load('modules');
                    }
                } catch (\Exception $e) {
                    Log::error('Failed to verify PayMongo session on success', [
                        'order_id' => $orderId,
                        'error'    => $e->getMessage(),
                    ]);
                }
            }

            return Inertia::render('shop/payment/PaymentSuccess', [
                'order'   => $order ? [
                    'id'           => $order->id,
                    'status'       => $order->status,
                    'shop_name'    => $order->shop_name,
                    'owner_name'   => $order->owner_name,
                    'email'        => $order->email,
                    'phone'        => $order->phone,
                    'block_street' => $order->block_street,
                    'municipality' => $order->municipality,
                    'barangay'     => $order->barangay,
                    'postal_code'  => $order->postal_code,
                    'branch_name'  => $order->branch_name,
                    'total_price'  => $order->total_price,
                    'created_at'   => $order->created_at,
                    'modules'      => $order->modules->map(fn($m) => [
                        'name'  => $m->name,
                        'price' => $m->price,
                    ]),
                ] : null,
                'payment' => $payment,
            ]);
        }

        return Inertia::render('shop/payment/PaymentSuccess');
    }

    public function cancel(): Response
    {
        return Inertia::render('shop/payment/PaymentCancel');
    }
}
