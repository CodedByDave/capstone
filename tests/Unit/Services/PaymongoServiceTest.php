<?php

use App\Models\Order;
use App\Services\PaymongoService;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

uses(TestCase::class);

it('sends the national mobile number because PayMongo supplies the country prefix', function () {
    Http::fake([
        'https://api.paymongo.com/v1/checkout_sessions' => Http::response([
            'data' => [
                'id' => 'cs_test_phone_format',
                'attributes' => [
                    'checkout_url' => 'https://checkout.paymongo.test/session',
                ],
            ],
        ], 200),
    ]);

    $order = new Order([
        'owner_name' => 'Test Owner',
        'email' => 'owner@example.com',
        'phone' => '+63-912-3456-789',
        'shop_name' => 'Test Laundry',
        'total_price' => 100,
        'payment_method' => 'gcash',
    ]);
    $order->id = 1;
    $order->public_id = 'test-order';

    app(PaymongoService::class)->createCheckoutSession($order);

    Http::assertSent(function (Request $request) {
        return $request->url() === 'https://api.paymongo.com/v1/checkout_sessions'
            && $request['data']['attributes']['billing']['phone'] === '9123456789';
    });
});
