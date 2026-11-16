<?php

namespace App\Services;

use App\Mail\OrderReadyMail;
use App\Models\ShopOrder;
use App\Notifications\OrderNotification;
use App\Repositories\ShopOrderRepository;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Notification;
use Throwable;

/**
 * What happens when an order is marked Completed: the owner and staff are told the laundry is done,
 * and a customer who gave an email is emailed automatically. A customer without an email is left to
 * the shop, who can call or text them from their own phone.
 */
class OrderReadyNotificationService
{
    public function __construct(private readonly ShopOrderRepository $orders) {}

    public function notifyCompleted(ShopOrder $order): void
    {
        $order->loadMissing('shop');
        $emailing = $this->shouldEmailCustomer($order);

        Notification::send(
            $this->orders->onlineOrderRecipients($order->shop),
            new OrderNotification($order, 'laundry_done', emailing: $emailing),
        );

        if ($emailing) {
            // Sent after the response so staff never wait on the mail server and no queue worker is needed.
            dispatch(fn () => $this->sendEmail($order->id))->afterResponse();
        }
    }

    public function shouldEmailCustomer(ShopOrder $order): bool
    {
        return filled($order->customer_email) && $order->ready_email_sent_at === null;
    }

    /** What the order page shows beside the manual contact options. */
    public function panel(ShopOrder $order): array
    {
        return [
            'email' => $order->customer_email,
            'email_sent_at' => $order->ready_email_sent_at?->toIso8601String(),
            'phone' => $order->customer_phone,
            'message' => $this->manualMessage($order),
        ];
    }

    /** The text staff can send from their own phone when the customer left no email. */
    public function manualMessage(ShopOrder $order): string
    {
        $order->loadMissing('shop');
        $first = strtok(trim((string) $order->customer_name), ' ') ?: 'there';
        $what = $order->isProductOrder() ? 'your products' : 'your laundry';
        $news = $order->deliver_to_customer ? 'is done and will be delivered to you soon.' : 'is ready for pickup.';
        $balance = $order->balanceDue();

        return "{$order->shop?->shop_name}: Hi {$first}, {$what} (Order {$order->order_number}) {$news}"
            .($balance > 0 ? ' Balance due: P'.number_format($balance, 2).'.' : '')
            .' Thank you!';
    }

    private function sendEmail(int $orderId): void
    {
        $order = $this->orders->findById($orderId);

        // Reopened, deleted or already emailed before this ran: nothing to send.
        if (! $order || $order->status !== 'completed' || ! $this->shouldEmailCustomer($order)) {
            return;
        }

        try {
            Mail::to($order->customer_email)->send(new OrderReadyMail($order->loadMissing('shop')));
            $this->orders->update($order, ['ready_email_sent_at' => now()]);
        } catch (Throwable $e) {
            // The order is already completed; staff can still contact the customer themselves.
            Log::warning('Order ready email failed', ['order_id' => $orderId, 'error' => $e->getMessage()]);
        }
    }
}
