<?php

namespace App\Services;

use App\Models\ShopOrder;
use App\Models\User;
use App\Notifications\OrderNotification;
use App\Repositories\ShopOrderRepository;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

/**
 * A customer buying a shop's retail products without any laundry. The order is collected at the
 * shop; its price is final when it is placed, so it can be paid online right away or in cash at
 * the shop. Products are put aside from stock when the order is placed and go back when it is
 * cancelled (see CustomerOrderCancellationService).
 */
class CustomerProductOrderService
{
    public function __construct(
        private readonly ShopOrderRepository $orders,
        private readonly CustomerAdditionalChargeService $items,
        private readonly OrderBillingBreakdownService $billing,
    ) {}

    /**
     * The price of the picked products before ordering, with the shop's tax.
     *
     * @param  list<array{id: int|string, count: int|string}>  $selection
     *
     * @throws ModelNotFoundException for a shop that is not active
     * @throws ValidationException when a pick is not a product the shop sells
     */
    public function estimate(int $shopId, array $selection): array
    {
        $shop = $this->orders->findActiveShop($shopId);

        if (! $shop) {
            throw (new ModelNotFoundException)->setModel(ShopOrder::class);
        }

        return $this->billing->productsEstimate($shop->id, $this->items->resolveRetail($shop->id, $selection));
    }

    /**
     * @param  array{shop_id: int|string, retail_items: list<array{id: int|string, count: int|string}>, customer_name: string, customer_phone: string, special_instructions?: string|null}  $data
     *
     * @throws ValidationException when the shop is unavailable or closed, or a product is not available
     */
    public function place(User $customer, array $data): ShopOrder
    {
        $order = DB::transaction(function () use ($customer, $data): ShopOrder {
            $shop = $this->orders->findActiveShop((int) $data['shop_id']);

            if (! $shop) {
                throw ValidationException::withMessages(['shop_id' => 'The selected shop is unavailable.']);
            }

            if (! $shop->isOpenAt()) {
                throw ValidationException::withMessages([
                    'shop_id' => $shop->shop_name.' is closed right now, so it cannot take new orders. Check its opening days and hours.',
                ]);
            }

            $lines = $this->items->resolveRetail($shop->id, $data['retail_items'], lock: true);

            if ($lines === []) {
                throw ValidationException::withMessages(['retail_items' => 'Add at least one product to your order.']);
            }

            $estimate = $this->billing->productsEstimate($shop->id, $lines);

            $order = $this->orders->create([
                'shop_id' => $shop->id,
                'user_id' => $customer->id,
                'order_source' => 'online',
                'order_type' => 'products',
                'service_id' => null,
                'order_number' => $this->orderNumber(),
                'customer_name' => $data['customer_name'],
                'customer_phone' => $data['customer_phone'],
                'special_instructions' => $data['special_instructions'] ?? null,
                // Collected at the shop; nothing is weighed or counted.
                'pickup_type' => 'walk_in',
                'pricing_model' => 'per_piece',
                'additional_charges' => $estimate['additional_charges'],
                'discount_amount' => 0,
                'tax_rate' => $estimate['tax_rate'],
                'tax_amount' => $estimate['tax'],
                'total_amount' => $estimate['total'],
                'payment_method' => 'cash',
                'payment_status' => 'unpaid',
                'amount_paid' => 0,
                'status' => 'pending',
            ]);

            $this->items->attachToOrder($order, $lines);

            $order->setRelation('shop', $shop);
            $customer->notify(new OrderNotification($order, 'placed'));

            return $order;
        });

        Notification::send(
            $this->orders->onlineOrderRecipients($order->shop),
            new OrderNotification($order, 'new_online_order'),
        );

        return $order;
    }

    private function orderNumber(): string
    {
        do {
            $number = 'PRD-'.now()->format('Ymd').'-'.strtoupper(Str::random(5));
        } while ($this->orders->findByOrderNumber($number));

        return $number;
    }
}
