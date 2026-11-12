<?php

use App\Enums\AccountType;
use App\Http\Middleware\EnforceOwnerPlatformPermissions;
use App\Http\Middleware\EnsureCurrentBusinessAgreementAccepted;
use App\Models\Shop;
use App\Models\ShopOrder;
use App\Models\ShopService;
use App\Models\SmsMessage;
use App\Models\User;
use App\Services\ShopOrderService;
use App\Services\Sms\PhilippinePhoneNumber;
use App\Services\Sms\ShopSmsService;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;

beforeEach(function () {
    $this->withoutMiddleware([
        EnsureCurrentBusinessAgreementAccepted::class,
        EnforceOwnerPlatformPermissions::class,
    ]);

    // No platform sender unless a test turns it on, so the own-phone tests stay on their own.
    config([
        'services.sms.driver' => 'textbee',
        'services.textbee.key' => null,
        'services.textbee.device_id' => null,
        'services.philsms.token' => null,
    ]);
});

const TEXTBEE_URL = 'https://api.textbee.dev/api/v1/gateway/send-sms';

function usePlatformTextBee(): void
{
    config(['services.textbee.key' => 'tb-key-123', 'services.textbee.base_url' => 'https://api.textbee.dev/api/v1']);
}

const SMS_CLOUD_URL = 'https://api.sms-gate.app/3rdparty/v1/messages';

/** A shop with its Android phone connected through the cloud server unless told otherwise. */
function smsShop(bool $enabled = true, string $name = 'Suds Laundry'): array
{
    $owner = User::factory()->create(['role' => AccountType::ShopOwner->value]);
    $shop = Shop::create([
        'owner_id' => $owner->id,
        'shop_name' => $name,
        'phone' => '09171234567',
        'municipality' => 'Imus',
        'barangay' => 'Poblacion',
        'postal_code' => '4103',
        'status' => 'active',
        'location_mode' => 'single',
        'setup_completed_at' => now(),
        'sms_gateway_enabled' => $enabled,
        'sms_gateway_mode' => 'cloud',
        'sms_gateway_username' => 'GATEUSER',
        'sms_gateway_password' => 'gate-secret',
        'sms_notify_ready' => true,
    ]);
    $service = ShopService::create([
        'shop_id' => $shop->id,
        'service_name' => 'Wash and Fold',
        'is_active' => true,
        'pricing_model' => 'per_kg',
        'price_per_kg' => 60,
    ]);

    return compact('owner', 'shop', 'service');
}

/** A ₱300 walk-in order with no customer account, as created at the counter. */
function smsOrder(array $ctx, array $extra = []): ShopOrder
{
    return app(ShopOrderService::class)->createOrder(array_merge([
        'shop_id' => $ctx['shop']->id,
        'customer_name' => 'Henry Santos',
        'customer_phone' => '0917 555 1234',
        'service_id' => $ctx['service']->id,
        'pickup_type' => 'walk_in',
        'pricing_model' => 'per_kg',
        'actual_weight_kg' => 5,
        'price_per_kg' => 60,
        'additional_charges' => 0,
        'discount_amount' => 0,
        'total_amount' => 300,
        'customer_agreement_version' => '1.0',
        'customer_agreement_accepted' => true,
    ], $extra), $ctx['owner']);
}

function completeOrder(ShopOrder $order): void
{
    $order->update(['status' => 'in_progress']);
    app(ShopOrderService::class)->updateStatus($order->fresh(), 'completed');
}

// ─── Phone numbers ─────────────────────────────────────────────────────────────

test('philippine mobile numbers are turned into +63 form', function (?string $input, ?string $expected) {
    expect(PhilippinePhoneNumber::toE164($input))->toBe($expected);
})->with([
    ['09175551234', '+639175551234'],
    ['0917 555 1234', '+639175551234'],
    ['9175551234', '+639175551234'],
    ['639175551234', '+639175551234'],
    ['+63 917-555-1234', '+639175551234'],
    ['(046) 123 4567', null],
    ['12345', null],
    [null, null],
]);

// ─── Automatic text when the order is completed ───────────────────────────────

test('a walk-in customer is texted through the cloud gateway when the order is completed', function () {
    Http::fake([SMS_CLOUD_URL => Http::response(['id' => 'msg-1', 'state' => 'Pending'], 202)]);
    $ctx = smsShop();
    $order = smsOrder($ctx);

    completeOrder($order);
    $this->app->terminate(); // the text goes out after the response

    Http::assertSent(fn (Request $request) => $request->url() === SMS_CLOUD_URL
        && $request->hasHeader('Authorization', 'Basic '.base64_encode('GATEUSER:gate-secret'))
        && $request['phoneNumbers'] === ['+639175551234']
        && str_starts_with($request['textMessage']['text'], 'Suds Laundry: Hi Henry, your laundry (Order '.$order->order_number.') is ready for pickup.')
        && str_contains($request['textMessage']['text'], 'Balance due: P300.00'));

    $sms = SmsMessage::sole();
    expect($sms->status)->toBe('sent')
        ->and($sms->gateway_message_id)->toBe('msg-1')
        ->and($sms->shop_order_id)->toBe($order->id)
        ->and($sms->sent_by)->toBeNull();
});

test('nothing is texted when the shop has texting off', function () {
    Http::fake();
    $ctx = smsShop(enabled: false);

    completeOrder(smsOrder($ctx));
    $this->app->terminate();

    Http::assertNothingSent();
    expect(SmsMessage::count())->toBe(0);
});

test('nothing is texted when automatic texts are switched off', function () {
    Http::fake();
    $ctx = smsShop();
    $ctx['shop']->update(['sms_notify_ready' => false]);

    completeOrder(smsOrder($ctx));
    $this->app->terminate();

    Http::assertNothingSent();
});

test('a delivery order is texted that it is on the way, not to come and pick up', function () {
    Http::fake([SMS_CLOUD_URL => Http::response(['id' => 'msg-d'], 202), '*' => Http::response([], 200)]);
    $ctx = smsShop();
    $order = smsOrder($ctx);
    $order->update(['deliver_to_customer' => true]);

    completeOrder($order);
    $this->app->terminate();

    Http::assertSent(fn (Request $request) => $request->url() === SMS_CLOUD_URL
        && str_starts_with($request['textMessage']['text'], 'Suds Laundry: Hi Henry')
        && str_contains($request['textMessage']['text'], 'is done and will be delivered to you soon.')
        && ! str_contains($request['textMessage']['text'], 'pickup'));
    expect(SmsMessage::sole()->status)->toBe('sent');
});

test('only marking an order completed sends a text, so credit is used once per order', function () {
    Http::fake([SMS_CLOUD_URL => Http::response(['id' => 'msg-x'], 202)]);
    $ctx = smsShop();
    $order = smsOrder($ctx);

    app(ShopOrderService::class)->updateStatus($order->fresh(), 'in_progress');
    $this->app->terminate();
    expect(SmsMessage::count())->toBe(0);

    app(ShopOrderService::class)->updateStatus($order->fresh(), 'completed');
    $this->app->terminate();
    expect(SmsMessage::count())->toBe(1);
});

test('a phone that cannot be reached is recorded and does not stop the order', function () {
    Http::fake([SMS_CLOUD_URL => Http::response(['message' => 'device offline'], 500)]);
    $ctx = smsShop();
    $order = smsOrder($ctx);

    completeOrder($order);
    $this->app->terminate();

    expect($order->fresh()->status)->toBe('completed')
        ->and(SmsMessage::sole()->status)->toBe('failed')
        ->and(SmsMessage::sole()->error)->toContain('device offline');
});

// ─── Text customer button ──────────────────────────────────────────────────────

test('staff can text the customer from the order page', function () {
    Http::fake([SMS_CLOUD_URL => Http::response(['id' => 'msg-2'], 202)]);
    $ctx = smsShop();
    $order = smsOrder($ctx);
    $order->update(['status' => 'completed']);

    $this->actingAs($ctx['owner'])
        ->post("/shop/operations/orders/{$order->payment_public_id}/notify-ready")
        ->assertSessionHasNoErrors()
        ->assertSessionHas('toast.type', 'success');

    expect(SmsMessage::sole()->sent_by)->toBe($ctx['owner']->id);
});

test('a failed send tells staff why', function () {
    Http::fake([SMS_CLOUD_URL => Http::response([], 401)]);
    $ctx = smsShop();
    $order = smsOrder($ctx);

    $this->actingAs($ctx['owner'])
        ->post("/shop/operations/orders/{$order->payment_public_id}/notify-ready")
        ->assertSessionHasErrors(['sms' => 'The SMS gateway rejected the username or password.']);
});

test('the button needs texting to be set up', function () {
    Http::fake();
    $ctx = smsShop(enabled: false);
    $order = smsOrder($ctx);

    $this->actingAs($ctx['owner'])
        ->post("/shop/operations/orders/{$order->payment_public_id}/notify-ready")
        ->assertSessionHasErrors('sms');

    Http::assertNothingSent();
});

test('another shop cannot text this order', function () {
    Http::fake();
    $mine = smsShop(name: 'Mine');
    $other = smsShop(name: 'Other');
    $order = smsOrder($mine);

    $this->actingAs($other['owner'])
        ->post("/shop/operations/orders/{$order->payment_public_id}/notify-ready")
        ->assertNotFound();

    Http::assertNothingSent();
});

test('the order page shows the last text', function () {
    Http::fake([SMS_CLOUD_URL => Http::response(['id' => 'msg-3'], 202)]);
    $ctx = smsShop();
    $order = smsOrder($ctx);
    app(ShopSmsService::class)->notifyReady($order, $ctx['owner']);

    $this->actingAs($ctx['owner'])->get("/shop/operations/orders/{$order->payment_public_id}")
        ->assertInertia(fn ($page) => $page
            ->where('smsNotice.enabled', true)
            ->where('smsNotice.phone', '+639175551234')
            ->where('smsNotice.last.status', 'sent')
            ->where('smsNotice.last.sent_by', $ctx['owner']->name)
            ->missing('shopOrder.shop.sms_gateway_password'));
});

// ─── Settings ──────────────────────────────────────────────────────────────────

test('the owner connects a phone and the password is stored encrypted', function () {
    $ctx = smsShop(enabled: false);
    $ctx['shop']->update(['sms_gateway_username' => null, 'sms_gateway_password' => null]);

    $this->actingAs($ctx['owner'])
        ->patch('/shop/settings/sms', [
            'enabled' => true,
            'mode' => 'local',
            'url' => 'http://192.168.1.20:8080',
            'username' => 'sms',
            'password' => 'phone-pass',
            'notify_ready' => true,
        ])
        ->assertSessionHasNoErrors();

    $shop = $ctx['shop']->fresh();
    expect($shop->sms_gateway_enabled)->toBeTrue()
        ->and($shop->sms_gateway_url)->toBe('http://192.168.1.20:8080')
        ->and($shop->sms_gateway_password)->toBe('phone-pass')
        ->and(\Illuminate\Support\Facades\DB::table('shops')->where('id', $shop->id)->value('sms_gateway_password'))->not->toBe('phone-pass');
});

test('a blank password keeps the saved one', function () {
    $ctx = smsShop();

    $this->actingAs($ctx['owner'])
        ->patch('/shop/settings/sms', ['enabled' => true, 'mode' => 'cloud', 'username' => 'GATEUSER', 'password' => '', 'notify_ready' => false])
        ->assertSessionHasNoErrors();

    expect($ctx['shop']->fresh()->sms_gateway_password)->toBe('gate-secret');
});

test('texting cannot be turned on without a login', function () {
    $ctx = smsShop(enabled: false);
    $ctx['shop']->update(['sms_gateway_username' => null, 'sms_gateway_password' => null]);

    $this->actingAs($ctx['owner'])
        ->patch('/shop/settings/sms', ['enabled' => true, 'mode' => 'cloud', 'notify_ready' => true])
        ->assertSessionHasErrors('username');
});

test('local mode needs the phone address', function () {
    $ctx = smsShop();

    $this->actingAs($ctx['owner'])
        ->patch('/shop/settings/sms', ['enabled' => true, 'mode' => 'local', 'url' => '', 'notify_ready' => true])
        ->assertSessionHasErrors('url');
});

test('the settings page never sends the password', function () {
    $ctx = smsShop();

    $this->actingAs($ctx['owner'])->get('/shop/settings')
        ->assertInertia(fn ($page) => $page
            ->where('smsGateway.available', true)
            ->where('smsGateway.own_phone.enabled', true)
            ->where('smsGateway.own_phone.has_password', true)
            ->missing('smsGateway.own_phone.password'));
});

test('a test text goes to the local phone address', function () {
    Http::fake(['http://192.168.1.20:8080/message' => Http::response(['id' => 'local-1'], 202)]);
    $ctx = smsShop();
    $ctx['shop']->update(['sms_gateway_mode' => 'local', 'sms_gateway_url' => 'http://192.168.1.20:8080/']);

    $this->actingAs($ctx['owner'])
        ->post('/shop/settings/sms/test', ['phone' => '09181234567'])
        ->assertSessionHasNoErrors();

    Http::assertSent(fn (Request $request) => $request->url() === 'http://192.168.1.20:8080/message'
        && $request['phoneNumbers'] === ['+639181234567']);
    expect(SmsMessage::sole()->purpose)->toBe('test');
});

test('staff cannot change the sms settings', function () {
    $ctx = smsShop(enabled: false);
    $staff = User::factory()->create(['role' => AccountType::Staff->value]);

    $this->actingAs($staff)->patch('/shop/settings/sms', ['enabled' => true, 'mode' => 'cloud', 'notify_ready' => true]);

    expect($ctx['shop']->fresh()->sms_gateway_enabled)->toBeFalse();
});

test('the order page never sends the shop secrets to the browser', function () {
    $ctx = smsShop();
    $ctx['shop']->update(['paymongo_secret_key' => 'sk_test_SECRETVALUE123', 'paymongo_public_key' => 'pk_test_PUBLIC123']);
    $order = smsOrder($ctx);

    $response = $this->actingAs($ctx['owner'])->get("/shop/operations/orders/{$order->payment_public_id}");

    $response->assertOk()
        ->assertDontSee('sk_test_SECRETVALUE123')
        ->assertDontSee('gate-secret')
        ->assertInertia(fn ($page) => $page
            ->missing('shopOrder.shop.paymongo_secret_key')
            ->missing('shopOrder.shop.sms_gateway_password'));

    // Still usable on the server for creating PayMongo checkouts.
    expect($ctx['shop']->fresh()->paymongo_secret_key)->toBe('sk_test_SECRETVALUE123');
});

// ─── Platform sender: TextBee ──────────────────────────────────────────────────

test('with the platform sender set up, a shop with nothing configured texts through TextBee', function () {
    usePlatformTextBee();
    Http::fake([TEXTBEE_URL => Http::response(['data' => ['success' => true, 'smsBatchId' => 'batch-9', 'recipientCount' => 1]])]);
    $ctx = smsShop(enabled: false);
    $order = smsOrder($ctx);

    completeOrder($order);
    $this->app->terminate();

    Http::assertSent(fn (Request $request) => $request->url() === TEXTBEE_URL
        && $request->hasHeader('x-api-key', 'tb-key-123')
        && $request['recipients'] === ['+639175551234']
        && str_starts_with($request['message'], 'Suds Laundry: Hi Henry'));

    $sms = SmsMessage::sole();
    expect($sms->provider)->toBe('textbee')
        ->and($sms->status)->toBe('sent')
        ->and($sms->gateway_message_id)->toBe('batch-9');
});

test('a shop using its own phone does not go through TextBee', function () {
    usePlatformTextBee();
    Http::fake([SMS_CLOUD_URL => Http::response(['id' => 'own-1'], 202), TEXTBEE_URL => Http::response([], 500)]);
    $ctx = smsShop(enabled: true);

    completeOrder(smsOrder($ctx));
    $this->app->terminate();

    Http::assertNotSent(fn (Request $request) => $request->url() === TEXTBEE_URL);
    expect(SmsMessage::sole()->provider)->toBe('smsgate');
});

test('a used-up TextBee limit is recorded with a plain reason', function () {
    usePlatformTextBee();
    Http::fake([TEXTBEE_URL => Http::response(['message' => 'Monthly limit reached'], 429)]);
    $ctx = smsShop(enabled: false);
    $order = smsOrder($ctx);

    completeOrder($order);
    $this->app->terminate();

    expect($order->fresh()->status)->toBe('completed')
        ->and(SmsMessage::sole()->status)->toBe('failed')
        ->and(SmsMessage::sole()->error)->toBe('The daily or monthly texting limit is used up.');
});

test('staff can text through the platform sender from the order page', function () {
    usePlatformTextBee();
    Http::fake([TEXTBEE_URL => Http::response(['data' => ['success' => true, 'smsBatchId' => 'batch-1']])]);
    $ctx = smsShop(enabled: false);
    $order = smsOrder($ctx);

    $this->actingAs($ctx['owner'])
        ->post("/shop/operations/orders/{$order->payment_public_id}/notify-ready")
        ->assertSessionHasNoErrors();

    expect(SmsMessage::sole()->provider)->toBe('textbee');
});

test('the owner only switches texting on or off', function () {
    usePlatformTextBee();
    $ctx = smsShop(enabled: false);
    $ctx['shop']->update(['sms_gateway_username' => null, 'sms_gateway_password' => null]);

    $this->actingAs($ctx['owner'])
        ->patch('/shop/settings/sms', ['notify_ready' => false])
        ->assertSessionHasNoErrors();

    expect($ctx['shop']->fresh()->sms_notify_ready)->toBeFalse();

    $this->actingAs($ctx['owner'])->get('/shop/settings')
        ->assertInertia(fn ($page) => $page
            ->where('smsGateway.available', true)
            ->where('smsGateway.platform_available', true)
            ->where('smsGateway.own_phone.enabled', false));
});

// ─── One-tap prompt when nothing is sent on its own ────────────────────────────

test('completing an order with no texting asks staff to text the customer', function () {
    Http::fake();
    $ctx = smsShop(enabled: false);
    $order = smsOrder($ctx);
    $order->update(['status' => 'in_progress']);

    $this->actingAs($ctx['owner'])
        ->patch("/shop/operations/orders/{$order->payment_public_id}/status", ['status' => 'completed'])
        ->assertSessionHas('ready_prompt', true);

    $this->actingAs($ctx['owner'])
        ->withSession(['ready_prompt' => true])
        ->get("/shop/operations/orders/{$order->payment_public_id}")
        ->assertInertia(fn ($page) => $page->where('readyPrompt', true));

    Http::assertNothingSent();
});

test('no prompt when the text goes out on its own', function () {
    usePlatformTextBee();
    Http::fake([TEXTBEE_URL => Http::response(['data' => ['success' => true, 'smsBatchId' => 'b']])]);
    $ctx = smsShop(enabled: false);
    $order = smsOrder($ctx);
    $order->update(['status' => 'in_progress']);

    $this->actingAs($ctx['owner'])
        ->patch("/shop/operations/orders/{$order->payment_public_id}/status", ['status' => 'completed'])
        ->assertSessionHas('ready_prompt', false)
        ->assertSessionHas('toast.message', fn ($message) => str_contains($message, 'is being texted'));
});

// ─── Platform sender: PhilSMS ──────────────────────────────────────────────────

const PHILSMS_URL = 'https://app.philsms.com/api/v3/sms/send';

function usePlatformPhilSms(): void
{
    config([
        'services.sms.driver' => 'philsms',
        'services.philsms.token' => 'ps-token-1',
        'services.philsms.sender_id' => 'PhilSMS',
        'services.philsms.base_url' => 'https://app.philsms.com/api/v3',
    ]);
}

test('with SMS_DRIVER=philsms the customer is texted through PhilSMS', function () {
    usePlatformPhilSms();
    Http::fake([PHILSMS_URL => Http::response(['status' => 'success', 'data' => ['uid' => 'ps-uid-7']])]);
    $ctx = smsShop(enabled: false);
    $order = smsOrder($ctx);

    completeOrder($order);
    $this->app->terminate();

    Http::assertSent(fn (Request $request) => $request->url() === PHILSMS_URL
        && $request->hasHeader('Authorization', 'Bearer ps-token-1')
        && $request['recipient'] === '639175551234'
        && $request['sender_id'] === 'PhilSMS'
        && $request['type'] === 'plain'
        && str_starts_with($request['message'], 'Suds Laundry: Hi Henry')
        && str_contains($request['message'], 'is ready for pickup.'));

    $sms = SmsMessage::sole();
    expect($sms->provider)->toBe('philsms')
        ->and($sms->status)->toBe('sent')
        ->and($sms->gateway_message_id)->toBe('ps-uid-7');
});

test('a PhilSMS error such as no credit is recorded with its reason', function () {
    usePlatformPhilSms();
    Http::fake([PHILSMS_URL => Http::response(['status' => 'error', 'message' => 'You do not have enough sms unit'])]);
    $ctx = smsShop(enabled: false);
    $order = smsOrder($ctx);

    completeOrder($order);
    $this->app->terminate();

    expect($order->fresh()->status)->toBe('completed')
        ->and(SmsMessage::sole()->status)->toBe('failed')
        ->and(SmsMessage::sole()->error)->toBe('PhilSMS could not send the text: You do not have enough sms unit');
});

test('a wrong PhilSMS token is reported plainly', function () {
    usePlatformPhilSms();
    Http::fake([PHILSMS_URL => Http::response(['status' => 'error', 'message' => 'Unauthenticated.'], 401)]);
    $ctx = smsShop(enabled: false);
    $order = smsOrder($ctx);

    $this->actingAs($ctx['owner'])
        ->post("/shop/operations/orders/{$order->payment_public_id}/notify-ready")
        ->assertSessionHasErrors(['sms' => 'PhilSMS rejected the API token.']);
});

test('without a PhilSMS token texting is not available', function () {
    config(['services.sms.driver' => 'philsms', 'services.philsms.token' => null]);
    $ctx = smsShop(enabled: false);

    $this->actingAs($ctx['owner'])->get('/shop/settings')
        ->assertInertia(fn ($page) => $page->where('smsGateway.available', false));
});
