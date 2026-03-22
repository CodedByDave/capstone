<?php

namespace App\Services;

use App\Models\LowStockAlert;
use App\Repositories\LowStockAlertRepository;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

class LowStockAlertService
{
    public function __construct(
        private readonly LowStockAlertRepository $alertRepository,
    ) {}

    public function unread(int $shopId): Collection
    {
        return $this->alertRepository->unreadForShop($shopId);
    }

    public function unreadCount(int $shopId): int
    {
        return $this->alertRepository->unreadCountForShop($shopId);
    }

    public function paginate(int $shopId, int $perPage = 15): LengthAwarePaginator
    {
        return $this->alertRepository->paginateForShop($shopId, $perPage);
    }

    public function markAsRead(LowStockAlert $alert): void
    {
        $this->alertRepository->update($alert, ['status' => 'read']);
    }

    public function resolve(LowStockAlert $alert): void
    {
        $this->alertRepository->update($alert, [
            'status'      => 'resolved',
            'resolved_at' => now(),
        ]);
    }

    public function dismiss(LowStockAlert $alert): void
    {
        $this->alertRepository->update($alert, ['status' => 'dismissed']);
    }

    public function markAllReadForShop(int $shopId): void
    {
        $this->alertRepository
            ->query()
            ->where('shop_id', $shopId)
            ->where('status', 'unread')
            ->update(['status' => 'read']);
    }
}
