<?php

namespace App\Services;

use App\Models\Order;
use App\Models\ShopOrder;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class PaymongoService
{
    protected string $baseUrl = 'https://api.paymongo.com/v1';

    protected function client()
    {
        return Http::withBasicAuth(config('services.paymongo.secret_key'), '')
            ->accept('application/json')
            ->contentType('application/json')
            ->baseUrl($this->baseUrl)
            ->timeout(30);
    }

    protected function clientWithKey(string $secretKey)
    {
        return Http::withBasicAuth($secretKey, '')
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

        $methodMap = [
            'gcash'    => 'gcash',
            'maya'     => 'paymaya',
            'card'     => 'card',
            'grab_pay' => 'grab_pay',
            'dob'      => 'dob',
            'billease' => 'billease',
        ];

        $selectedMethod = $methodMap[$order->payment_method] ?? null;
        $paymentMethods = $selectedMethod
            ? [$selectedMethod]
            : ['card', 'gcash', 'paymaya', 'grab_pay'];

        $payload = [
            'data' => [
                'attributes' => [
                    'send_email_receipt' => true,
                    'show_description'   => true,
                    'show_line_items'    => true,
                    'billing'            => [
                        'name'  => $order->owner_name,
                        'email' => $order->email,
                        'phone' => $order->phone,
                    ],
                    'line_items' => [
                        [
                            'currency' => 'PHP',
                            'amount'   => $amount,
                            'name'     => "Laundry Modules for {$order->shop_name}",
                            'quantity' => 1,
                        ]
                    ],
                    'payment_method_types' => $paymentMethods,
                    'success_url'          => url('/shop/payment/success?order_id=' . $order->id),
                    'cancel_url'           => url('/shop/payment/cancel'),
                    'description'          => "Order #{$order->id} - {$order->shop_name}",
                    'metadata'             => [
                        'order_id'   => (string) $order->id,
                        'shop_name'  => $order->shop_name,
                        'owner_name' => $order->owner_name,
                    ],
                ]
            ]
        ];

        Log::info('PayMongo Checkout Request', [
            'order_id'        => $order->id,
            'amount_php'      => $order->total_price,
            'amount_centavos' => $amount,
            'payment_method'  => $order->payment_method,
            'payload'         => $payload,
        ]);

        $response = $this->client()->post('/checkout_sessions', $payload);

        Log::info('PayMongo Checkout Response', [
            'status' => $response->status(),
            'body'   => $response->json(),
        ]);

        if ($response->failed()) {
            $error = $response->json();

            Log::error('PayMongo Checkout Failed', [
                'order_id' => $order->id,
                'status'   => $response->status(),
                'error'    => $error,
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
                'error'      => $error,
            ]);

            throw new \Exception('Failed to retrieve checkout session');
        }

        return $response->json();
    }

    /**
     * Extract the first PayMongo payment ID from a checkout session response.
     * Returns null if the session has no completed payments yet.
     */
    public function extractPaymentId(array $session): ?string
    {
        return $session['data']['attributes']['payment_intent']['attributes']['payments'][0]['id'] ?? null;
    }

    /**
     * Issue a refund for a PayMongo payment.
     *
     * @param  string $paymentId      The PayMongo payment ID (pay_xxxx)
     * @param  int    $amountCentavos Amount in centavos (₱1 = 100 centavos)
     * @param  string $reason         'duplicate' | 'fraudulent' | 'others'
     */
    public function refund(string $paymentId, int $amountCentavos, string $reason = 'others'): array
    {
        Log::info('PayMongo Refund Request', [
            'payment_id' => $paymentId,
            'amount'     => $amountCentavos,
            'reason'     => $reason,
        ]);

        $response = $this->client()->post('/refunds', [
            'data' => [
                'attributes' => [
                    'amount'     => $amountCentavos,
                    'payment_id' => $paymentId,
                    'reason'     => $reason,
                ],
            ],
        ]);

        Log::info('PayMongo Refund Response', [
            'status' => $response->status(),
            'body'   => $response->json(),
        ]);

        if ($response->failed()) {
            $error = $response->json();

            Log::error('PayMongo Refund Failed', [
                'payment_id' => $paymentId,
                'status'     => $response->status(),
                'error'      => $error,
            ]);

            $message = $error['errors'][0]['detail'] ?? $error['errors'][0]['code'] ?? 'Refund request failed';
            throw new \Exception($message);
        }

        return $response->json();
    }

    /**
     * Create a PayMongo checkout session for a shop order using that shop's own secret key.
     * Money lands directly in the shop's PayMongo account.
     */
    public function createShopOrderSession(ShopOrder $order, string $secretKey): array
    {
        $amount = (int) round((float) $order->total_amount * 100);

        if ($amount < 10000) {
            throw new \Exception('Minimum payment amount is ₱100.00');
        }

        $payload = [
            'data' => [
                'attributes' => [
                    'send_email_receipt' => false,
                    'show_description'   => true,
                    'show_line_items'    => true,
                    'line_items' => [
                        [
                            'currency' => 'PHP',
                            'amount'   => $amount,
                            'name'     => "Laundry Order {$order->order_number}",
                            'quantity' => 1,
                        ],
                    ],
                    'payment_method_types' => ['gcash', 'paymaya', 'card', 'grab_pay', 'dob'],
                    'success_url' => url("/user/orders/{$order->id}/payment/success"),
                    'cancel_url'  => url("/user/orders/{$order->id}/payment/cancel"),
                    'description' => "Order {$order->order_number} — {$order->customer_name}",
                    'metadata'    => [
                        'order_id'     => (string) $order->id,
                        'order_number' => $order->order_number,
                        'shop_id'      => (string) $order->shop_id,
                    ],
                ],
            ],
        ];

        Log::info('PayMongo ShopOrder Checkout Request', [
            'order_id'        => $order->id,
            'amount_php'      => $order->total_amount,
            'amount_centavos' => $amount,
        ]);

        $response = $this->clientWithKey($secretKey)->post('/checkout_sessions', $payload);

        Log::info('PayMongo ShopOrder Checkout Response', [
            'status' => $response->status(),
            'body'   => $response->json(),
        ]);

        if ($response->failed()) {
            $error        = $response->json();
            $errorMessage = $error['errors'][0]['detail'] ?? $error['errors'][0]['code'] ?? 'Failed to create checkout session';

            Log::error('PayMongo ShopOrder Checkout Failed', [
                'order_id' => $order->id,
                'error'    => $error,
            ]);

            throw new \Exception($errorMessage);
        }

        return $response->json();
    }

    /**
     * Test whether a PayMongo secret key is valid by calling the events endpoint.
     * Returns true if authenticated (key is valid), false if 401 or request fails.
     */
    public function verifySecretKey(string $key): bool
    {
        try {
            $response = $this->clientWithKey($key)->get('/events?limit=1');
            return $response->status() !== 401;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Retrieve a checkout session using a shop's own secret key.
     */
    public function getShopOrderSession(string $sessionId, string $secretKey): array
    {
        $response = $this->clientWithKey($secretKey)->get("/checkout_sessions/{$sessionId}");

        if ($response->failed()) {
            Log::error('Failed to retrieve ShopOrder checkout session', [
                'session_id' => $sessionId,
                'error'      => $response->json(),
            ]);

            throw new \Exception('Failed to retrieve checkout session');
        }

        return $response->json();
    }
}
