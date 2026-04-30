<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\ShopOrder;
use App\Services\PaymongoService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Log;

class UserPaymentController extends Controller
{
    public function __construct(private PaymongoService $paymongo) {}

    /**
     * Initiate an online PayMongo payment for the order.
     * Uses the shop's own PayMongo secret key so revenue lands in their account.
     */
    public function pay(ShopOrder $order): RedirectResponse
    {
        abort_if($order->user_id !== auth()->id(), 403);
        abort_if($order->payment_status === 'paid', 422);
        abort_if($order->status === 'cancelled', 422);

        $shop = $order->shop;

        if (!$shop || !$shop->hasPaymongo()) {
            return back()->with('toast', [
                'type'    => 'error',
                'message' => 'Online payment is not set up for this shop.',
            ]);
        }

        if ((float) $order->total_amount < 100) {
            return back()->with('toast', [
                'type'    => 'error',
                'message' => 'Order total must be at least ₱100 to pay online.',
            ]);
        }

        try {
            $session = $this->paymongo->createShopOrderSession($order, $shop->paymongo_secret_key);
        } catch (\Exception $e) {
            Log::error('ShopOrder PayMongo session creation failed', [
                'order_id' => $order->id,
                'error'    => $e->getMessage(),
            ]);

            return back()->with('toast', [
                'type'    => 'error',
                'message' => 'Could not initiate payment: ' . $e->getMessage(),
            ]);
        }

        $checkoutUrl = $session['data']['attributes']['checkout_url'];
        $sessionId   = $session['data']['id'];

        $order->update([
            'paymongo_session_id' => $sessionId,
            'payment_method'      => 'online',
        ]);

        return redirect($checkoutUrl);
    }

    /**
     * PayMongo redirects here after the customer completes payment.
     */
    public function success(ShopOrder $order): RedirectResponse
    {
        abort_if($order->user_id !== auth()->id(), 403);

        if ($order->payment_status === 'paid') {
            return redirect()->route('user.orders.show', $order)->with('toast', [
                'type'    => 'success',
                'message' => 'Payment already confirmed.',
            ]);
        }

        $shop = $order->shop;

        if (!$shop || !$shop->hasPaymongo() || !$order->paymongo_session_id) {
            return redirect()->route('user.orders.show', $order)->with('toast', [
                'type'    => 'error',
                'message' => 'Could not verify payment. Please contact the shop.',
            ]);
        }

        try {
            $session   = $this->paymongo->getShopOrderSession($order->paymongo_session_id, $shop->paymongo_secret_key);
            $status    = $session['data']['attributes']['status'] ?? null;
            $paymentId = $this->paymongo->extractPaymentId($session);

            if ($status === 'completed') {
                $order->update([
                    'payment_status'      => 'paid',
                    'amount_paid'         => $order->total_amount,
                    'paid_at'             => now(),
                    'paymongo_payment_id' => $paymentId,
                ]);

                Log::info('ShopOrder paid via PayMongo', [
                    'order_id'   => $order->id,
                    'payment_id' => $paymentId,
                ]);

                return redirect()->route('user.orders.show', $order)->with('toast', [
                    'type'    => 'success',
                    'message' => 'Payment successful! Your order is now paid.',
                ]);
            }

            return redirect()->route('user.orders.show', $order)->with('toast', [
                'type'    => 'warning',
                'message' => 'Payment is still processing. Please wait a moment and refresh.',
            ]);

        } catch (\Exception $e) {
            Log::error('ShopOrder PayMongo verification failed', [
                'order_id' => $order->id,
                'error'    => $e->getMessage(),
            ]);

            return redirect()->route('user.orders.show', $order)->with('toast', [
                'type'    => 'error',
                'message' => 'Could not verify payment. Please contact the shop.',
            ]);
        }
    }

    /**
     * PayMongo redirects here if the customer cancels on their checkout page.
     */
    public function cancel(ShopOrder $order): RedirectResponse
    {
        abort_if($order->user_id !== auth()->id(), 403);

        return redirect()->route('user.orders.show', $order)->with('toast', [
            'type'    => 'warning',
            'message' => 'Payment was cancelled. You can try again anytime.',
        ]);
    }
}
