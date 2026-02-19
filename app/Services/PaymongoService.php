<?php

namespace App\Services;

use App\Models\Order;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class PaymongoService
{
    protected string $baseUrl = 'https://api.paymongo.com/v1';

    protected function client()
    {
        return Http::withBasicAuth(env('PAYMONGO_SECRET_KEY'), '')
            ->accept('application/json')
            ->contentType('application/json')
            ->baseUrl($this->baseUrl)
            ->timeout(30);
    }

    public function createCheckoutSession(Order $order): array
    {
        $amount = (int) round($order->total_price * 100);

        if ($amount < 10000) {
            throw new \Exception('Minimum order amount is ₱100.00');
        }

        $payload = [
            'data' => [
                'attributes' => [
                    'send_email_receipt' => true,
                    'show_description'   => true,
                    'show_line_items'    => true,
                    'line_items'         => [
                        [
                            'currency' => 'PHP',
                            'amount'   => $amount,
                            'name'     => "Laundry Modules for {$order->shop_name}",
                            'quantity' => 1,
                        ]
                    ],
                    'payment_method_types' => [
                        'card',
                        'gcash',
                        'paymaya',
                        'grab_pay'
                    ],
                    'success_url' => url('/shop/payment/success?order_id=' . $order->id),
                    'cancel_url'  => url('/shop/payment/cancel'),
                    'description' => "Order #{$order->id} - {$order->shop_name}",
                    'metadata'    => [
                        'order_id'   => (string) $order->id,
                        'shop_name'  => $order->shop_name,
                        'owner_name' => $order->owner_name,
                    ]
                ]
            ]
        ];

        Log::info('PayMongo Checkout Request', [
            'order_id'        => $order->id,
            'amount_php'      => $order->total_price,
            'amount_centavos' => $amount,
            'payload'         => $payload
        ]);

        $response = $this->client()->post('/checkout_sessions', $payload);

        Log::info('PayMongo Checkout Response', [
            'status' => $response->status(),
            'body'   => $response->json()
        ]);

        if ($response->failed()) {
            $error = $response->json();

            Log::error('PayMongo Checkout Failed', [
                'order_id' => $order->id,
                'status'   => $response->status(),
                'error'    => $error
            ]);

            $errorMessage = 'Failed to create checkout session';

            if (isset($error['errors'][0]['detail'])) {
                $errorMessage = $error['errors'][0]['detail'];
            } elseif (isset($error['errors'][0]['code'])) {
                $errorMessage = $error['errors'][0]['code'];
            }

            throw new \Exception($errorMessage);
        }

        return $response->json();
    }

    public function getCheckoutSession(string $checkoutSessionId): array
    {
        Log::info('Retrieving checkout session', ['session_id' => $checkoutSessionId]);

        $response = $this->client()->get("/checkout_sessions/{$checkoutSessionId}");

        if ($response->failed()) {
            $error = $response->json();
            Log::error('Failed to retrieve checkout session', [
                'session_id' => $checkoutSessionId,
                'error'      => $error
            ]);

            throw new \Exception('Failed to retrieve checkout session');
        }

        return $response->json();
    }
}
