<?php

namespace App\Repositories;

use App\Models\Order;
use App\Models\Payment;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;

class OrderRepository extends Repository
{
    public function __construct(Order $order)
    {
        parent::__construct($order);
    }

    public function markAsPaid(Order $order): bool
    {
        return $this->update($order, ['status' => 'paid']);
    }

    public function findForApproval(int $orderId): Order
    {
        return Order::query()
            ->with('user')
            ->lockForUpdate()
            ->findOrFail($orderId);
    }

    public function markApproved(Order $order): void
    {
        $order->update(['status' => 'approved']);
    }

    public function refresh(Order $order): Order
    {
        return $order->refresh()->load('user');
    }

    public function create(array $data): Order
    {
        return Order::create(collect($data)->except('modules')->toArray());
    }

    public function hasOpenApplication(int $userId): bool
    {
        return Order::query()
            ->where('user_id', $userId)
            ->whereIn('status', ['approved', 'paid', 'pending'])
            ->exists();
    }

    public function findActiveSubscriptionForUser(int $userId): ?Order
    {
        return Order::query()
            ->where('user_id', $userId)
            ->activeSubscription()
            ->where('expires_at', '>', now())
            ->latest()
            ->first();
    }

    public function countRejectedApplications(int $userId): int
    {
        return Order::query()
            ->where('user_id', $userId)
            ->where('status', 'rejected')
            ->where('is_upgrade', false)
            ->where('is_trial', false)
            ->count();
    }

    public function addModules(Order $order, array $moduleNames): void
    {
        foreach ($moduleNames as $moduleName) {
            $order->modules()->create([
                'name' => $moduleName,
                'price' => 0,
            ]);
        }
    }

    public function updateKycDocuments(Order $order, array $paths): void
    {
        $order->update($paths);
    }

    public function findLatestApprovedPayableForUser(int $userId): Order
    {
        return Order::query()
            ->where('user_id', $userId)
            ->where('status', 'approved')
            ->where('is_trial', false)
            ->latest()
            ->firstOrFail();
    }

    public function setPaymentMethod(Order $order, string $paymentMethod): void
    {
        $order->update(['payment_method' => $paymentMethod]);
    }

    public function findWithModules(int $orderId): Order
    {
        return Order::query()->with('modules')->findOrFail($orderId);
    }

    public function markPaidWithExpiry(Order $order): void
    {
        $order->update([
            'status' => 'paid',
            'expires_at' => now()->addMonths((int) $order->billing_months),
        ]);
    }

    public function expireOtherActiveSubscriptions(Order $order): void
    {
        Order::query()
            ->where('user_id', $order->user_id)
            ->activeSubscription()
            ->where('id', '!=', $order->id)
            ->update(['status' => 'expired']);
    }

    public function refreshWithModules(Order $order): Order
    {
        return $order->fresh()->load('modules');
    }

    // ── Admin index ───────────────────────────────────────────────────────────

    public function getPaginated(array $filters = [], int $perPage = 20): LengthAwarePaginator
    {
        $query = $this->query()->with(['user', 'modules', 'payments']);

        return $this->applyFilters($query, $filters)
            ->when(
                $this->sortColumn($filters),
                fn (Builder $query, string $column) => $query->orderBy($column, $this->sortDirection($filters)),
                fn (Builder $query) => $query->latest(),
            )
            ->paginate($perPage)
            ->withQueryString();
    }

    public function getForCsvExport(array $filters = []): Collection
    {
        $query = Order::query()->with('user');

        return $this->applyFilters($query, $filters)
            ->when(
                $this->sortColumn($filters),
                fn (Builder $query, string $column) => $query->orderBy($column, $this->sortDirection($filters)),
                fn (Builder $query) => $query->latest(),
            )
            ->get();
    }

    private function applyFilters(Builder $query, array $filters): Builder
    {

        if (! empty($filters['search'])) {
            $query->where(function ($q) use ($filters) {
                $q->where('shop_name', 'like', "%{$filters['search']}%")
                    ->orWhere('owner_name', 'like', "%{$filters['search']}%")
                    ->orWhere('email', 'like', "%{$filters['search']}%")
                    ->orWhere('transaction_reference', 'like', "%{$filters['search']}%");
            });
        }

        if (! empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (! empty($filters['plan'])) {
            $query->where('plan_name', $filters['plan']);
        }

        if (! empty($filters['date'])) {
            $query->whereDate('created_at', $filters['date']);
        }

        return $query;
    }

    private function sortColumn(array $filters): ?string
    {
        return match ($filters['sort_by'] ?? null) {
            'shop' => 'shop_name',
            'owner' => 'owner_name',
            'total' => 'total_price',
            'expires' => 'expires_at',
            default => null,
        };
    }

    private function sortDirection(array $filters): string
    {
        return ($filters['sort_direction'] ?? null) === 'desc' ? 'desc' : 'asc';
    }

    public function getStats(): array
    {
        $paymentRevenue = (float) Payment::where('status', 'paid')->sum('amount');
        $legacyOrderRevenue = (float) Order::where('status', 'paid')
            ->where('total_price', '>', 0)
            ->whereDoesntHave('payments', fn (Builder $query) => $query->where('status', 'paid'))
            ->sum('total_price');

        return [
            'total' => Order::count(),
            'paid' => Order::where('status', 'paid')->count(),
            'approved' => Order::where('status', 'approved')->count(),
            'rejected' => Order::where('status', 'rejected')->count(),
            'pending' => Order::where('status', 'pending')->count(),
            'expired' => Order::activeSubscription()
                ->where('expires_at', '<', now())
                ->count(),
            'revenue' => $paymentRevenue + $legacyOrderRevenue,
        ];
    }

    public function findWithRelations(int $id): Order
    {
        return Order::with(['user', 'modules', 'payments'])->findOrFail($id);
    }

    public function loadAdminDetails(Order $order): Order
    {
        return $order->load(['user', 'modules', 'payments']);
    }
}
