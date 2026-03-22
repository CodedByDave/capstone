<?php

namespace App\Repositories;

use App\Models\Order;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

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

    public function create(array $data): Order
    {
        return Order::create(collect($data)->except('modules')->toArray());
    }

    // ── Admin index ───────────────────────────────────────────────────────────

    public function getPaginated(array $filters = [], int $perPage = 20): LengthAwarePaginator
    {
        $query = $this->query()
            ->with(['user', 'modules', 'payments'])
            ->latest();

        if (!empty($filters['search'])) {
            $query->where(function ($q) use ($filters) {
                $q->where('shop_name',  'like', "%{$filters['search']}%")
                  ->orWhere('owner_name','like', "%{$filters['search']}%")
                  ->orWhere('email',     'like', "%{$filters['search']}%");
            });
        }

        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (!empty($filters['plan'])) {
            $query->where('subscription_plan', $filters['plan']);
        }

        if (!empty($filters['date'])) {
            $query->whereDate('created_at', $filters['date']);
        }

        return $query->paginate($perPage)->withQueryString();
    }

    public function getStats(): array
    {
        return [
            'total'   => Order::count(),
            'paid'    => Order::where('status', 'paid')->count(),
            'pending' => Order::where('status', 'pending')->count(),
            'expired' => Order::where('status', 'paid')
                ->where('expires_at', '<', now())
                ->count(),
            'revenue' => (float) Order::where('status', 'paid')->sum('total_price'),
        ];
    }

    public function findWithRelations(int $id): Order
    {
        return Order::with(['user', 'modules', 'payments'])->findOrFail($id);
    }
}
