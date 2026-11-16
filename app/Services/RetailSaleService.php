<?php

namespace App\Services;

use App\Models\ShopOrder;
use App\Models\User;
use App\Repositories\ShopOrderRepository;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * Retail products sold on their own, with no laundry: walk-in sales staff record at the counter,
 * and the products-only orders customers place online. They live on the Retail Products page, apart
 * from the laundry orders. A counter sale is paid in full when it is recorded and is handed over on
 * the spot, so it is saved already collected.
 */
class RetailSaleService
{
    public function __construct(
        private readonly ShopOrderRepository $orders,
        private readonly CustomerAdditionalChargeService $items,
        private readonly OrderBillingBreakdownService $billing,
        private readonly OrderPaymentService $payments,
        private readonly CustomerShopCatalogService $catalog,
        private readonly CustomerProductOrderService $orderNumbers,
        private readonly OrderTaxService $tax,
        private readonly ShopContext $context,
    ) {}

    /** The sales list and the numbers shown above it. */
    public function listing(User $actor, array $filters): array
    {
        $shopId = $this->context->shopIdFor($actor);
        $today = $this->orders->productSalesSince($shopId, now()->startOfDay());

        return [
            'sales' => $this->orders->paginateByShop($shopId, $filters['per_page'], $filters),
            'summary' => [
                'sales_today' => $today['count'],
                'revenue_today' => $today['total'],
                'open_online' => $this->orders->countOpenOnlineProducts($shopId),
                'by_status' => $this->orders->countByStatus($shopId, 'products'),
            ],
        ];
    }

    /** What the sale form needs: the products on sale, the shop's tax and the payment methods. */
    public function formData(User $actor): array
    {
        $shopId = $this->context->shopIdFor($actor);

        return [
            'products' => $this->catalog->retailProductOptions($shopId),
            'tax' => $this->tax->forShop($shopId),
            'paymentMethods' => ShopOrder::PAYMENT_METHODS,
        ];
    }

    /**
     * Records a counter sale: the products leave stock, the full price (with the shop's tax) is
     * taken as a payment and the sale is saved as collected.
     *
     * @param  array{retail_items: list<array{id: int|string, count: int|string}>, customer_name?: string|null, customer_phone?: string|null, customer_email?: string|null, payment_method: string, amount_tendered?: float|string|null}  $data
     *
     * @throws ValidationException when a product is unavailable, out of stock, or the cash is short
     */
    public function record(User $actor, array $data): ShopOrder
    {
        $shopId = $this->context->shopIdFor($actor);

        return DB::transaction(function () use ($actor, $shopId, $data): ShopOrder {
            $lines = $this->items->resolveRetail($shopId, $data['retail_items'], lock: true);

            if ($lines === []) {
                throw ValidationException::withMessages(['retail_items' => 'Add at least one product to the sale.']);
            }

            $estimate = $this->billing->productsEstimate($shopId, $lines);
            $total = (float) $estimate['total'];
            $method = $data['payment_method'];

            // Only cash is handed over, so GCash and Maya payments have no cash received or change.
            $tendered = $method === 'cash' && filled($data['amount_tendered'] ?? null)
                ? round((float) $data['amount_tendered'], 2)
                : null;

            if ($tendered !== null && $tendered < $total) {
                throw ValidationException::withMessages([
                    'amount_tendered' => 'The cash received is less than the total of ₱'.number_format($total, 2).'.',
                ]);
            }

            $order = $this->orders->create([
                'shop_id' => $shopId,
                'user_id' => null,
                'order_source' => 'shop',
                'order_type' => 'products',
                'service_id' => null,
                'order_number' => $this->orderNumbers->orderNumber(),
                'customer_name' => $data['customer_name'] ?? 'Walk-in customer',
                'customer_phone' => $data['customer_phone'] ?? null,
                'customer_email' => $data['customer_email'] ?? null,
                'pickup_type' => 'walk_in',
                'pricing_model' => 'per_piece',
                'additional_charges' => $estimate['additional_charges'],
                'discount_amount' => 0,
                'tax_rate' => $estimate['tax_rate'],
                'tax_amount' => $estimate['tax'],
                'total_amount' => $total,
                'payment_method' => $method,
                'payment_status' => 'unpaid',
                'amount_paid' => 0,
                // Handed over at the counter: nothing is left to prepare or collect.
                'status' => 'picked_up',
                'completed_at' => now(),
                'picked_up_at' => now(),
            ]);

            $this->items->attachToOrder($order, $lines);
            $this->payments->recordInitialPayment($actor, $order, $total, $method, $tendered);

            return $order->refresh();
        });
    }
}
