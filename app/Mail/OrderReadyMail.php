<?php

namespace App\Mail;

use App\Models\ShopOrder;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

/** Tells a customer their laundry (or products) is done, sent automatically when the order completes. */
class OrderReadyMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public ShopOrder $order) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "{$this->order->shop->shop_name}: your order {$this->order->order_number} is ready",
        );
    }

    public function content(): Content
    {
        return new Content(view: 'emails.order-ready', with: [
            'name' => strtok(trim((string) $this->order->customer_name), ' ') ?: 'there',
            'shopName' => $this->order->shop->shop_name,
            'orderNumber' => $this->order->order_number,
            'what' => $this->order->isProductOrder() ? 'products' : 'laundry',
            'delivery' => (bool) $this->order->deliver_to_customer,
            'balance' => $this->order->balanceDue(),
        ]);
    }
}
