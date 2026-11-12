<?php

namespace App\Services;

use App\Models\ShopOrder;
use Barryvdh\DomPDF\Facade\Pdf;

/**
 * The invoice receipt of a shop order: what the customer ordered, what it costs and what has been
 * paid so far. The same data fills the on-screen receipt and the downloadable PDF.
 */
class OrderReceiptService
{
    private const METHOD_LABELS = [
        'cash' => 'Cash',
        'gcash' => 'GCash',
        'maya' => 'Maya',
        'card' => 'Card',
    ];

    public function __construct(
        private readonly OrderBillingBreakdownService $billing,
        private readonly OrderPaymentService $payments,
    ) {}

    /** @return array<string, mixed> */
    public function forOrder(ShopOrder $order): array
    {
        $order->loadMissing(['shop', 'service', 'serviceLines', 'supplies']);

        $billing = $this->billing->forOrder($order);
        $status = $order->payment_status;

        $confirmed = $this->payments->history($order)->filter(fn ($payment) => $payment->isConfirmed());
        $method = $confirmed->last()?->method ?? $order->payment_method;

        // Cash handed over and the change given back, from the latest payment that recorded them.
        $cashPayment = $confirmed->last(fn ($payment) => $payment->amount_tendered !== null);

        $items = [];
        // A products order has no laundry service: each product is its own line.
        $isProducts = $billing['service'] === null;
        $serviceLines = $isProducts ? $billing['extra_services'] : [$billing['service'], ...$billing['extra_services']];
        foreach ($serviceLines as $line) {
            $items[] = [
                'label' => $this->lineLabel($line),
                'amount' => (float) $line['amount'],
            ];
        }
        if ($isProducts) {
            foreach ($billing['supplies'] ?? [] as $supply) {
                $items[] = ['label' => $supply['name'], 'amount' => (float) ($supply['amount'] ?? 0)];
            }
        } elseif ($billing['additional_charges'] > 0) {
            $items[] = ['label' => 'Additional charges', 'amount' => (float) $billing['additional_charges']];
        }
        if (($billing['rush_fee'] ?? 0) > 0) {
            $items[] = ['label' => 'Rush fee', 'amount' => (float) $billing['rush_fee']];
        }
        if ($billing['discount'] > 0) {
            $items[] = ['label' => 'Discount', 'amount' => -(float) $billing['discount']];
        }
        if ($billing['tax'] > 0 && $billing['tax_label']) {
            $items[] = ['label' => $billing['tax_label'], 'amount' => (float) $billing['tax']];
        }
        if (($billing['pickup_fee'] ?? 0) > 0) {
            $items[] = ['label' => 'Pickup charges', 'amount' => (float) $billing['pickup_fee']];
        }
        if ($billing['delivery_fee'] > 0) {
            $items[] = ['label' => 'Delivery charges', 'amount' => (float) $billing['delivery_fee']];
        }

        return [
            'title' => match ($status) {
                'paid' => 'Payment Successful',
                'partial' => 'Partial Payment',
                default => 'Order Invoice',
            },
            'shop_name' => $order->shop?->shop_name,
            'customer_name' => $order->customer_name,
            'invoice_number' => 'INV-'.$order->order_number,
            'order_number' => $order->order_number,
            'order_time' => $order->created_at?->format('h:i A, j F Y'),
            'payment_method' => $method ? (self::METHOD_LABELS[$method] ?? ucfirst((string) $method)) : 'Not paid yet',
            'payment_status' => $status,
            'payment_status_label' => match ($status) {
                'paid' => 'Paid',
                'partial' => 'Partial',
                default => 'Unpaid',
            },
            'amount_paid' => (float) $billing['amount_paid'],
            'amount_tendered' => $cashPayment ? (float) $cashPayment->amount_tendered : null,
            'change_given' => $cashPayment ? (float) $cashPayment->change_given : null,
            'items' => $items,
            'total' => (float) $billing['total'],
            'balance_due' => (float) $billing['balance_due'],
            'is_estimate' => (bool) $billing['is_estimate'],
        ];
    }

    public function pdf(ShopOrder $order): string
    {
        return Pdf::loadView('pdf.order-receipt', ['receipt' => $this->forOrder($order)])
            ->setPaper([0, 0, 302, 560])
            ->output();
    }

    private function lineLabel(array $line): string
    {
        $quantity = rtrim(rtrim(number_format((float) $line['quantity'], 2, '.', ''), '0'), '.');
        $plural = (float) $line['quantity'] !== 1.0;
        $unit = match ($line['unit']) {
            'bundle' => $plural ? 'bundles' : 'bundle',
            'piece' => $plural ? 'pieces' : 'piece',
            default => $line['unit'],
        };

        return "{$line['name']} x{$quantity} {$unit}";
    }
}
