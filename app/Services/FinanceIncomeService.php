<?php

namespace App\Services;

use App\Models\ShopOrderPayment;
use App\Repositories\OrderPaymentRepository;
use App\Repositories\ShopOrderRepository;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

/** Income figures for the finance pages, taken from the payments actually received. */
class FinanceIncomeService
{
    public function __construct(
        private readonly OrderPaymentRepository $payments,
        private readonly ShopOrderRepository $orders,
    ) {}

    public function paginate(int $shopId, ?string $from, ?string $to, ?string $method): LengthAwarePaginator
    {
        return $this->payments->paginateIncome($shopId, $from, $to, $method)
            ->through(fn (ShopOrderPayment $payment) => $this->row($payment));
    }

    /** @return array{total_income: float, this_month_income: float, total_count: int, unpaid_count: int} */
    public function stats(int $shopId): array
    {
        $now = Carbon::now();

        return [
            'total_income' => $this->payments->incomeTotal($shopId),
            'this_month_income' => $this->payments->incomeTotal(
                $shopId,
                $now->copy()->startOfMonth()->toDateString(),
                $now->copy()->endOfMonth()->toDateString(),
            ),
            'total_count' => $this->payments->paidOrderCount($shopId),
            'unpaid_count' => $this->orders->countUnpaid($shopId),
        ];
    }

    /** Income lines for the transactions page, one per payment received in the period. */
    public function transactionRows(int $shopId, string $from, string $to): Collection
    {
        return $this->payments->incomeBetween($shopId, $from, $to)->map(fn (ShopOrderPayment $payment) => [
            'id' => 'income-'.$payment->public_id,
            'type' => 'income',
            'date' => ($payment->paid_at ?? $payment->created_at)->toDateString(),
            'title' => $payment->order->customer_name.' — '.($payment->order->service?->service_name ?? 'Order'),
            'reference' => $payment->order->order_number,
            'category' => $payment->method ?? '—',
            'amount' => (float) $payment->amount,
        ]);
    }

    /** @return array<string, mixed> */
    private function row(ShopOrderPayment $payment): array
    {
        return [
            'id' => $payment->public_id,
            'order_number' => $payment->order->order_number,
            'customer_name' => $payment->order->customer_name,
            'service' => $payment->order->service ? [
                'id' => $payment->order->service->id,
                'service_name' => $payment->order->service->service_name,
            ] : null,
            'amount_paid' => number_format((float) $payment->amount, 2, '.', ''),
            'payment_method' => $payment->method,
            'paid_at' => $payment->paid_at?->toIso8601String(),
        ];
    }
}
