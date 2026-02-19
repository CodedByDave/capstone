<?php

namespace App\Services;

use App\Models\Order;
use Exception;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class StripeService
{
    protected string $baseUrl = 'https://api.stripe.com/v1';

    public function createCheckoutSession(Order $order): array
    {
        $amount = (int) round($order->total_price * 100);

        if ($amount < 10000) {
            throw new Exception('Minimum order amount is ₱100.');
        }

        $payload = [
            'mode'                 => 'payment',
            'line_items'           => [
                [
                    'price_data' => [
                        'currency'     => 'php',
                        'product_data' => [
                            'name'        => "Laundry Modules for {$order->shop_name}",
                            'description' => "Order #{$order->id}",
                        ],
                        'unit_amount'  => $amount,
                    ],
                    'quantity'   => 1,
                ],
            ],
            'payment_method_types' => ['card'],
            'success_url'          => route('payment.success', ['order_id' => $order->id]),
            'cancel_url'           => route('payment.cancel'),
            'customer_email'       => $order->email,
            'metadata'             => [
                'order_id'   => (string) $order->id,
                'shop_name'  => $order->shop_name,
                'owner_name' => $order->owner_name,
            ],
        ];

        // http_build_query correctly serializes nested arrays for Stripe's
        // form-encoded API (e.g. line_items[0][price_data][currency]=php)
        $encoded = http_build_query($payload);

        Log::info('Stripe Checkout Request', [
            'order_id'        => $order->id,
            'amount_php'      => $order->total_price,
            'amount_centavos' => $amount,
        ]);

        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . config('services.stripe.secret_key'),
            'Content-Type'  => 'application/x-www-form-urlencoded',
        ])
        ->withBody($encoded, 'application/x-www-form-urlencoded')
        ->timeout(30)
        ->post("{$this->baseUrl}/checkout/sessions");

        Log::info('Stripe Checkout Response', [
            'status' => $response->status(),
            'body'   => $response->json(),
        ]);

        if ($response->failed()) {
            $error        = $response->json();
            $errorMessage = $error['error']['message'] ?? 'Failed to create Stripe checkout session.';

            Log::error('Stripe Checkout Failed', [
                'order_id' => $order->id,
                'status'   => $response->status(),
                'error'    => $error,
            ]);

            throw new Exception($errorMessage);
        }

        $session = $response->json();

        return [
            'data' => [
                'id'         => $session['id'],
                'attributes' => [
                    'checkout_url' => $session['url'],
                ],
            ],
        ];
    }
}
