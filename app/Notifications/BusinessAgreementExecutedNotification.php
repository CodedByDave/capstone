<?php

namespace App\Notifications;

use App\Models\BusinessAgreementAcceptance;
use Illuminate\Notifications\Notification;

class BusinessAgreementExecutedNotification extends Notification
{
    public function __construct(
        public readonly BusinessAgreementAcceptance $acceptance,
    ) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toDatabase(object $notifiable): array
    {
        return [
            'type' => 'business_agreement_executed',
            'title' => 'Agreement fully executed',
            'body' => "LaundryHub countersigned the agreement for {$this->acceptance->business_name}. Your fully signed copy is ready in Legal Documents.",
            'agreement_acceptance_public_id' => $this->acceptance->public_id,
            'url' => route('shop.agreement.show'),
        ];
    }
}
