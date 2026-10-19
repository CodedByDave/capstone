<?php

namespace App\Services;

use App\Exceptions\ShopOrderPaymentException;
use App\Models\User;
use Illuminate\Support\Facades\Log;

class CustomerOrderPlacementService
{
    public function __construct(
        private readonly ShopOrderService $orderService,
        private readonly ShopOrderPaymentService $paymentService,
    ) {}

    public function place(
        User $customer,
        array $data,
        ?string $ipAddress,
        ?string $userAgent,
    ): array {
        $order = $this->orderService->createCustomerOrder(
            $customer,
            $data,
            $ipAddress,
            $userAgent,
        );

        if (($data['payment_method'] ?? 'cash') === 'cash') {
            return ['order' => $order, 'checkout_url' => null, 'payment_error' => null];
        }

        if ($order->actual_weight_kg === null) {
            return [
                'order' => $order,
                'checkout_url' => null,
                'payment_error' => null,
            ];
        }

        try {
            return [
                'order' => $order,
                'checkout_url' => $this->paymentService->initiate(
                    $customer,
                    (string) $order->payment_public_id,
                    (string) $order->payment_method,
                ),
                'payment_error' => null,
            ];
        } catch (ShopOrderPaymentException $exception) {
            return ['order' => $order, 'checkout_url' => null, 'payment_error' => $exception->getMessage()];
        } catch (\Throwable $exception) {
            Log::error('Checkout could not start after customer order placement', [
                'order_id' => $order->id,
                'error' => $exception->getMessage(),
            ]);

            return [
                'order' => $order,
                'checkout_url' => null,
                'payment_error' => 'The order was placed, but checkout could not start. Open the order and try Pay Online again.',
            ];
        }
    }
}
