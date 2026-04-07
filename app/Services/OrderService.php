<?php

namespace App\Services;

use App\Models\Order;
use App\Repositories\OrderRepository;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class OrderService
{
    private const PLAN_MODULES = [
        'Basic' => [
            'HRM',
            'Operations',
        ],
        'Standard' => [
            'HRM',
            'Operations',
            'Inventory Management',
            'Finance Management',
        ],
        'Premium' => [
            'HRM',
            'Operations',
            'Inventory Management',
            'Finance Management',
            'Reports & Analytics',
        ],
    ];

    public function __construct(
        protected OrderRepository $orderRepository
    ) {}

    public function create(array $data): Order
    {
        $planName         = $data['plan_name'];
        $billingMonths    = (int) $data['billing_months'];
        $subscriptionPlan = $billingMonths === 1 ? 'monthly' : 'annually';
        $expiresAt        = now()->addMonths($billingMonths);

        $order = $this->orderRepository->create([
            'user_id'           => $data['user_id'],
            'shop_name'         => $data['shop_name'],
            'owner_name'        => $data['owner_name'],
            'email'             => $data['email'],
            'phone'             => $data['phone'],
            'block_street'      => $data['block_street'] ?? null,
            'municipality'      => $data['municipality'],
            'barangay'          => $data['barangay'],
            'postal_code'       => $data['postal_code'],
            'total_price'       => $data['total_price'],
            'plan_name'         => $data['plan_name'],
            'billing_months'    => $data['billing_months'],
            'status'            => 'pending',
            'subscription_plan' => $subscriptionPlan,
            'expires_at'        => $expiresAt,
            'payment_method'    => $data['payment_method'] ?? null,
        ]);

        $modules = self::PLAN_MODULES[$planName] ?? [];
        foreach ($modules as $moduleName) {
            $order->modules()->create([
                'name'  => $moduleName,
                'price' => 0,
            ]);
        }

        return $order->load('modules');
    }

    public function getPaginated(array $filters = [], int $perPage = 20): LengthAwarePaginator
    {
        return $this->orderRepository->getPaginated($filters, $perPage);
    }

    public function getStats(): array
    {
        return $this->orderRepository->getStats();
    }

    public function find(int $id): Order
    {
        return $this->orderRepository->findWithRelations($id);
    }
}
