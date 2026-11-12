<?php

namespace App\Services;

use App\Models\ShopOrder;
use App\Models\ShopOrderPayment;
use App\Models\User;
use Illuminate\Database\Eloquent\ModelNotFoundException;

/**
 * The payment step after staff create an order at the counter: the order is saved first, then
 * the customer pays all, part or nothing of it, like a checkout after choosing the items.
 */
class OrderCheckoutService
{
    public function __construct(
        private readonly OrderPaymentService $payments,
        private readonly OrderReceiptService $receipts,
        private readonly ShopContext $shopContext,
    ) {}

    /** The order summary and balance shown on the payment step. */
    public function pageData(User $actor, ShopOrder $order): array
    {
        $this->assertOwnShop($actor, $order);

        return [
            // Not "order": that prop is the shared subscription the sidebar needs.
            'shopOrder' => [
                'public_id' => $order->payment_public_id,
                'order_number' => $order->order_number,
                'customer_name' => $order->customer_name,
                'pickup_type' => $order->pickup_type,
                'status' => $order->status,
            ],
            'summary' => $this->receipts->forOrder($order),
        ];
    }

    /**
     * Record what the customer paid. Nothing is recorded when they pay later.
     *
     * @param  array{payment_mode: string, payment_method?: string, amount?: mixed, amount_tendered?: mixed}  $data
     */
    public function pay(User $actor, ShopOrder $order, array $data): ?ShopOrderPayment
    {
        $this->assertOwnShop($actor, $order);

        if ($data['payment_mode'] === 'unpaid') {
            return null;
        }

        $amount = $data['payment_mode'] === 'full'
            ? $order->balanceDue()
            : round((float) $data['amount'], 2);

        $tendered = ($data['payment_method'] ?? null) === 'cash' && filled($data['amount_tendered'] ?? null)
            ? round((float) $data['amount_tendered'], 2)
            : null;

        return $this->payments->recordCounterPayment($actor, $order, $amount, $data['payment_method'], $tendered);
    }

    /** A walk-in customer is waiting at the counter, so their receipt opens straight away. */
    public function opensReceipt(ShopOrder $order): bool
    {
        return $order->pickup_type === 'walk_in';
    }

    private function assertOwnShop(User $actor, ShopOrder $order): void
    {
        if ($order->shop_id !== $this->shopContext->shopIdFor($actor)) {
            throw (new ModelNotFoundException)->setModel(ShopOrder::class);
        }
    }
}
