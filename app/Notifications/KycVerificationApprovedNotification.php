<?php

namespace App\Notifications;

use App\Models\KycVerification;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class KycVerificationApprovedNotification extends Notification
{
    public function __construct(
        public readonly KycVerification $verification,
        public readonly bool $trialStarted,
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    private function nextStep(): string
    {
        return $this->trialStarted
            ? 'Your free trial has started automatically. Finish setting up your shop to begin.'
            : 'You can now choose a plan from your dashboard.';
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Your business has been verified')
            ->greeting("Hello, {$notifiable->name}!")
            ->line("**{$this->verification->shop_name}** has been verified.")
            ->line($this->nextStep())
            ->salutation('— The Admin Team');
    }

    public function toDatabase(object $notifiable): array
    {
        return [
            'type' => 'kyc_verification_approved',
            'title' => 'Business verified',
            'body' => "{$this->verification->shop_name} has been verified. {$this->nextStep()}",
            'kyc_verification_id' => $this->verification->public_id,
        ];
    }
}
