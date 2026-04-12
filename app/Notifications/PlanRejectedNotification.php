<?php

namespace App\Notifications;

use App\Models\Order;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class PlanRejectedNotification extends Notification
{
    public function __construct(
        public readonly Order  $order,
        public readonly string $reason,
        public readonly bool   $refundIssued = false,
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $amount = '₱' . number_format($this->order->total_price, 2);

        $mail = (new MailMessage)
            ->subject('Your subscription plan order has been rejected')
            ->greeting("Hello, {$this->order->owner_name}!")
            ->line("We regret to inform you that your subscription plan order (#{$this->order->id}) for **{$this->order->shop_name}** has been rejected.")
            ->line('**Reason:** ' . $this->reason);

        if ($this->refundIssued) {
            $mail->line("**Refund:** A refund of {$amount} has been automatically processed back to your original payment method (e.g. GCash wallet). It may take 5–10 business days to reflect depending on your payment provider.");
        } else {
            $mail->line("**Refund:** A refund of {$amount} is being arranged. Our team will process it manually and you will receive a separate confirmation once done. This typically takes 5–7 business days.");
        }

        return $mail
            ->line('If you believe this rejection was made in error or would like to re-apply, please contact our support team.')
            ->salutation('— The Admin Team');
    }

    public function toDatabase(object $notifiable): array
    {
        $amount = '₱' . number_format($this->order->total_price, 2);

        $refundNote = $this->refundIssued
            ? "A refund of {$amount} has been automatically sent back to your payment method."
            : "A refund of {$amount} will be processed manually within 5–7 business days.";

        return [
            'type'             => 'plan_rejected',
            'title'            => 'Subscription plan rejected',
            'body'             => "Your order #{$this->order->id} for {$this->order->shop_name} was rejected. Reason: {$this->reason}. {$refundNote}",
            'order_id'         => $this->order->id,
            'rejection_reason' => $this->reason,
            'refund_amount'    => $this->order->total_price,
            'refund_issued'    => $this->refundIssued,
        ];
    }
}
