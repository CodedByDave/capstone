<?php

namespace App\Repositories;

use App\Models\Payment;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class PaymentRepository extends Repository
{
    public function __construct(Payment $payment)
    {
        parent::__construct($payment);
    }

    public function markAsPaid(Payment $payment, array $extra = []): bool
    {
        return $this->update($payment, array_merge([
            'status' => 'paid',
            'paid_at' => now(),
        ], $extra));
    }

    public function setPaymongoSessionId(Payment $payment, string $sessionId): void
    {
        $this->update($payment, ['paymongo_session_id' => $sessionId]);
    }

    public function findForOrder(int $orderId): ?Payment
    {
        return Payment::query()->where('order_id', $orderId)->latest()->first();
    }

    public function findPaidForOrder(int $orderId): ?Payment
    {
        return Payment::query()
            ->where('order_id', $orderId)
            ->where('status', 'paid')
            ->latest('paid_at')
            ->first();
    }

    public function paidHistoryForUser(int $userId, array $filters): LengthAwarePaginator
    {
        $sortColumns = [
            'paid_at' => 'payments.paid_at',
            'amount' => 'payments.amount',
            'payment_method' => 'payments.payment_method',
            'transaction_reference' => 'orders.transaction_reference',
            'plan_name' => 'orders.plan_name',
        ];
        $sortBy = $sortColumns[$filters['sort_by'] ?? 'paid_at'] ?? 'payments.paid_at';
        $sortDirection = ($filters['sort_direction'] ?? 'desc') === 'asc' ? 'asc' : 'desc';

        return Payment::query()
            ->join('orders', 'orders.id', '=', 'payments.order_id')
            ->select('payments.*')
            ->with('order:id,public_id,transaction_reference,shop_name,plan_name,billing_months,expires_at')
            ->where('orders.user_id', $userId)
            ->where('payments.status', 'paid')
            ->when($filters['search'] ?? null, function ($query, string $search) {
                $query->where(function ($query) use ($search) {
                    $query->where('orders.transaction_reference', 'like', "%{$search}%")
                        ->orWhere('orders.shop_name', 'like', "%{$search}%")
                        ->orWhere('orders.plan_name', 'like', "%{$search}%")
                        ->orWhere('payments.paymongo_payment_id', 'like', "%{$search}%");
                });
            })
            ->when($filters['payment_method'] ?? null, fn ($query, string $method) => $query->where('payments.payment_method', $method))
            ->orderBy($sortBy, $sortDirection)
            ->paginate($filters['per_page'] ?? 10)
            ->withQueryString();
    }

    public function paidSummaryForUser(int $userId): array
    {
        $summary = Payment::query()
            ->join('orders', 'orders.id', '=', 'payments.order_id')
            ->where('orders.user_id', $userId)
            ->where('payments.status', 'paid')
            ->selectRaw('COUNT(*) as payment_count, COALESCE(SUM(payments.amount), 0) as total_paid, MAX(payments.paid_at) as latest_paid_at')
            ->first();

        return [
            'payment_count' => (int) $summary->payment_count,
            'total_paid' => (float) $summary->total_paid,
            'latest_paid_at' => $summary->latest_paid_at,
        ];
    }
}
