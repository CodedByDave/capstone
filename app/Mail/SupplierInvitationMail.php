<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

/** Invites a supplier to set the password of the account a shop created for them. */
class SupplierInvitationMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public string $name,
        public string $shopName,
        public string $setPasswordUrl,
        public int $expiresInMinutes,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "{$this->shopName} added you as a supplier",
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.supplier-invitation',
        );
    }
}
