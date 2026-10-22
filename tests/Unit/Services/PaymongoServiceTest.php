<?php

use App\Models\Order;
use App\Services\PaymongoService;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

uses(TestCase::class);

it('sends the national mobile number because PayMongo supplies the country prefix', function () {
    config()->set('services.paymongo.secret_key', 'sk_test_ci');

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

it('charges only the balance due on a shop order checkout', function () {
    Http::fake([
        'https://api.paymongo.com/v1/checkout_sessions' => Http::response([
            'data' => ['id' => 'cs_test_balance', 'attributes' => ['checkout_url' => 'https://checkout.paymongo.test/balance']],
        ], 200),
    ]);

    // Laundry of 190 already paid; a 145 delivery fee was added afterwards.
    $order = new \App\Models\ShopOrder([
        'order_number' => 'ONL-BALANCE-1',
        'customer_name' => 'Balance Customer',
        'customer_phone' => '09181234567',
        'payment_method' => 'gcash',
        'total_amount' => 335,
        'amount_paid' => 190,
        'delivery_fee' => 145,
    ]);
    $order->payment_public_id = '01JBALANCEDUE0000000000000';

    app(PaymongoService::class)->createShopOrderSession($order, 'sk_test_shop');

    Http::assertSent(fn (Request $request) => $request['data']['attributes']['line_items'][0]['amount'] === 14500);
});
