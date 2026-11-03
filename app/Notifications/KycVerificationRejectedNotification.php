<?php

namespace App\Notifications;

use App\Models\KycVerification;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class KycVerificationRejectedNotification extends Notification
{
    public function __construct(public readonly KycVerification $verification) {}

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Your business verification needs corrections')
            ->greeting("Hello, {$notifiable->name}!")
            ->line("Your verification for **{$this->verification->shop_name}** could not be approved.")
            ->line('**Reason:** '.$this->verification->rejection_reason)
            ->line('Log in to correct the flagged items and resubmit. Everything else is kept as you submitted it.')
            ->salutation('— The Admin Team');
    }

    public function toDatabase(object $notifiable): array
    {
        return [
            'type' => 'kyc_verification_rejected',
            'title' => 'Verification needs corrections',
            'body' => "Your verification for {$this->verification->shop_name} was rejected. Reason: {$this->verification->rejection_reason}",
            'kyc_verification_id' => $this->verification->public_id,
            'rejection_reason' => $this->verification->rejection_reason,
        ];
    }
}
