<?php

namespace App\Services\Sms;

use App\Exceptions\SmsGatewayException;
use App\Jobs\SendOrderReadySms;
use App\Models\Shop;
use App\Models\ShopOrder;
use App\Models\SmsMessage;
use App\Models\User;
use App\Repositories\ShopRepository;
use App\Repositories\SmsMessageRepository;
use App\Services\ShopContext;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Validation\ValidationException;

/**
 * Texts customers that their laundry is ready. The platform sends for every shop (TextBee or
 * PhilSMS, chosen by SMS_DRIVER), so owners have nothing to set up; a shop may instead send from
 * its own phone (advanced).
 */
class ShopSmsService
{
    public const PROVIDER_OWN_PHONE = 'smsgate';

    public function __construct(
        private readonly PlatformSmsSender $platform,
        private readonly SmsGatewayClient $ownPhone,
        private readonly SmsMessageRepository $messages,
        private readonly ShopRepository $shops,
        private readonly ShopContext $shopContext,
    ) {}

    /** The settings card. The own-phone password is never sent back to the browser. */
    public function settingsData(User $owner): array
    {
        $shop = $this->shopForOwner($owner);

        return [
            'available' => $this->canText($shop),
            'platform_available' => $this->platform->isConfigured(),
            'notify_ready' => (bool) $shop->sms_notify_ready,
            'own_phone' => [
                'enabled' => (bool) $shop->sms_gateway_enabled,
                'mode' => $shop->sms_gateway_mode ?: SmsGatewayClient::MODE_CLOUD,
                'url' => $shop->sms_gateway_url,
                'username' => $shop->sms_gateway_username,
                'has_password' => filled($shop->sms_gateway_password),
            ],
        ];
    }

    /** A blank password keeps the saved one, so the owner does not retype it for every change. */
    public function updateSettings(User $owner, array $data): Shop
    {
        $shop = $this->shopForOwner($owner);

        $values = [
            'sms_notify_ready' => (bool) $data['notify_ready'],
            'sms_gateway_enabled' => (bool) $data['enabled'],
            'sms_gateway_mode' => $data['mode'],
            'sms_gateway_url' => $data['mode'] === SmsGatewayClient::MODE_LOCAL ? $data['url'] : null,
            'sms_gateway_username' => $data['username'] ?? null,
        ];

        if (filled($data['password'] ?? null)) {
            $values['sms_gateway_password'] = $data['password'];
        }

        $password = $values['sms_gateway_password'] ?? $shop->sms_gateway_password;

        if ($values['sms_gateway_enabled'] && (blank($values['sms_gateway_username']) || blank($password))) {
            throw ValidationException::withMessages([
                'username' => 'Enter the username and password shown in the SMS Gateway app to use your own phone.',
            ]);
        }

        return $this->shops->updateSmsGateway($shop, $values);
    }

    /** Sends a test text so the owner can see texting work. */
    public function sendTest(User $owner, string $phone): SmsMessage
    {
        $shop = $this->shopForOwner($owner);
        $number = PhilippinePhoneNumber::toE164($phone)
            ?? throw ValidationException::withMessages(['phone' => 'Enter a Philippine mobile number, like 0917 123 4567.']);

        if (! $this->canText($shop)) {
            throw ValidationException::withMessages(['phone' => 'Texting is not available yet.']);
        }

        $sms = $this->deliver(
            $shop,
            null,
            SmsMessage::PURPOSE_TEST,
            $number,
            "This is a test text from {$shop->shop_name}. Customers will be texted like this when their laundry is ready.",
            $owner,
        );

        $this->failIfNotSent($sms, 'phone');

        return $sms;
    }

    /** Whether this shop has a way to send texts: its own phone, or the platform sender. */
    public function canText(?Shop $shop): bool
    {
        return $shop !== null && ($shop->sms_gateway_enabled || $this->platform->isConfigured());
    }

    /**
     * Whether marking this order Completed should text the customer on its own. Walk-in and
     * delivery orders alike: only the wording differs. Completed is the only status that texts, so
     * each order uses one text of credit.
     */
    public function shouldAutoNotifyReady(ShopOrder $order): bool
    {
        $order->loadMissing('shop');

        return $this->canText($order->shop)
            && $order->shop->sms_notify_ready
            && PhilippinePhoneNumber::toE164($order->customer_phone) !== null;
    }

    /**
     * Whether staff should be prompted to text the customer themselves after marking the order
     * Completed, because no text is going out on its own.
     */
    public function needsManualReadyPrompt(ShopOrder $order): bool
    {
        return $order->status === 'completed'
            && PhilippinePhoneNumber::toE164($order->customer_phone) !== null
            && ! $this->shouldAutoNotifyReady($order);
    }

    /**
     * Sent after the response is returned, so staff never wait on the phone and the shop needs no
     * queue worker running.
     */
    public function queueReadyNotice(ShopOrder $order): void
    {
        SendOrderReadySms::dispatchAfterResponse($order->id);
    }

    /**
     * Text the customer that their laundry is ready for pickup. A failure is recorded and returned,
     * never thrown, so a phone that is off does not stop the order from being completed.
     */
    public function notifyReady(ShopOrder $order, ?User $actor = null): SmsMessage
    {
        $order->loadMissing('shop');
        $number = PhilippinePhoneNumber::toE164($order->customer_phone);

        return $this->deliver(
            $order->shop,
            $order,
            SmsMessage::PURPOSE_ORDER_READY,
            $number ?? (string) $order->customer_phone,
            $this->readyMessage($order),
            $actor,
            $number === null ? 'The customer phone number is not a valid mobile number.' : null,
        );
    }

    /** Staff pressing "Text customer" on an order: only for orders of their own shop. */
    public function notifyReadyByStaff(User $actor, ShopOrder $order): SmsMessage
    {
        if ($order->shop_id !== $this->shopContext->shopIdFor($actor)) {
            throw (new ModelNotFoundException)->setModel(ShopOrder::class);
        }

        $order->loadMissing('shop');

        if (! $this->canText($order->shop)) {
            throw ValidationException::withMessages([
                'sms' => 'Texting is not available. Use "Open SMS app" to send it from your phone.',
            ]);
        }

        $sms = $this->notifyReady($order, $actor);
        $this->failIfNotSent($sms, 'sms');

        return $sms;
    }

    /** What the order page shows: whether texting works, the message, and the last ready text sent. */
    public function orderPanel(ShopOrder $order): array
    {
        $order->loadMissing('shop');
        $last = $this->messages->latestReadyForOrder($order->id);

        return [
            'enabled' => $this->canText($order->shop),
            'phone' => PhilippinePhoneNumber::toE164($order->customer_phone),
            'message' => $this->readyMessage($order),
            'last' => $last ? [
                'status' => $last->status,
                'error' => $last->error,
                'sent_at' => $last->created_at?->toIso8601String(),
                'sent_by' => $last->sender?->name,
            ] : null,
        ];
    }

    public function readyMessage(ShopOrder $order): string
    {
        $order->loadMissing('shop');
        $first = strtok(trim((string) $order->customer_name), ' ') ?: 'there';
        $balance = $order->balanceDue();

        // A delivery order is brought to the customer; every other order is collected at the shop.
        $news = $order->deliver_to_customer
            ? 'is done and will be delivered to you soon.'
            : 'is ready for pickup.';

        // The sender shows the SMS service ("PhilSMS"), so the shop name leads the text: the customer
        // sees who it is from in the notification preview. Plain "P" instead of the peso sign keeps
        // the text in one 160-character SMS part.
        $what = $order->isProductOrder() ? 'your products' : 'your laundry';

        return "{$order->shop?->shop_name}: Hi {$first}, {$what} (Order {$order->order_number}) {$news}"
            .($balance > 0 ? ' Balance due: P'.number_format($balance, 2).'.' : '')
            .' Thank you!';
    }

    private function deliver(
        Shop $shop,
        ?ShopOrder $order,
        string $purpose,
        string $phone,
        string $text,
        ?User $actor,
        ?string $preError = null,
    ): SmsMessage {
        // A shop that set up its own phone sends from it; every other shop uses the platform sender.
        $provider = $shop->sms_gateway_enabled ? self::PROVIDER_OWN_PHONE : $this->platform->provider();
        $error = $preError;
        $gatewayId = null;

        if ($error === null) {
            try {
                $gatewayId = $provider === self::PROVIDER_OWN_PHONE
                    ? $this->ownPhone->send($shop, $phone, $text)
                    : $this->platform->send($phone, $text);
            } catch (SmsGatewayException $e) {
                $error = $e->getMessage();
            }
        }

        return $this->messages->create([
            'shop_id' => $shop->id,
            'shop_order_id' => $order?->id,
            'purpose' => $purpose,
            'provider' => $provider,
            'phone' => $phone,
            'message' => $text,
            'status' => $error === null ? SmsMessage::STATUS_SENT : SmsMessage::STATUS_FAILED,
            'gateway_message_id' => $gatewayId,
            'error' => $error === null ? null : mb_substr($error, 0, 255),
            'sent_by' => $actor?->id,
        ]);
    }

    private function failIfNotSent(SmsMessage $sms, string $field): void
    {
        if (! $sms->isSent()) {
            throw ValidationException::withMessages([$field => $sms->error]);
        }
    }

    private function shopForOwner(User $owner): Shop
    {
        return $this->shops->findByOwnerId($owner->id)
            ?? throw (new ModelNotFoundException)->setModel(Shop::class);
    }
}
