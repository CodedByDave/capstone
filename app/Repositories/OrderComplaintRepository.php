<?php

namespace App\Repositories;

use App\Models\OrderComplaint;
use App\Models\OrderComplaintPhoto;
use App\Models\ShopOrder;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;

class OrderComplaintRepository extends Repository
{
    private const SORTABLE = [
        'order_number' => 'shop_orders.order_number',
        'customer' => 'shop_orders.customer_name',
        'type' => 'order_complaints.type',
        'status' => 'order_complaints.status',
        'created_at' => 'order_complaints.created_at',
    ];

    public function __construct(OrderComplaint $model)
    {
        parent::__construct($model);
    }

    // ── Customer side ─────────────────────────────────────────────────────────

    public function createFor(ShopOrder $order, string $type, string $description): OrderComplaint
    {
        return OrderComplaint::query()->create([
            'shop_order_id' => $order->id,
            'shop_id' => $order->shop_id,
            'user_id' => $order->user_id,
            'type' => $type,
            'description' => $description,
            'status' => 'open',
        ]);
    }

    public function addPhoto(OrderComplaint $complaint, string $path): OrderComplaintPhoto
    {
        return $complaint->photos()->create(['path' => $path]);
    }

    /** Serializes concurrent filings on one order; call inside a transaction. */
    public function lockOrder(ShopOrder $order): void
    {
        ShopOrder::query()->whereKey($order->id)->lockForUpdate()->first();
    }

    /** Re-reads the complaint with a row lock; call inside a transaction. */
    public function lockForUpdate(OrderComplaint $complaint): OrderComplaint
    {
        return OrderComplaint::query()->whereKey($complaint->id)->lockForUpdate()->firstOrFail();
    }

    public function countActiveForOrder(ShopOrder $order): int
    {
        return OrderComplaint::query()
            ->where('shop_order_id', $order->id)
            ->whereIn('status', OrderComplaint::ACTIVE_STATUSES)
            ->count();
    }

    /** @return Collection<int, OrderComplaint> newest first, with photos */
    public function forOrder(ShopOrder $order): Collection
    {
        return OrderComplaint::query()
            ->with('photos')
            ->where('shop_order_id', $order->id)
            ->latest('id')
            ->get();
    }

    /** A photo only when it belongs to one of this customer's complaints. */
    public function findPhotoForCustomer(string $publicId, int $customerId): ?OrderComplaintPhoto
    {
        return OrderComplaintPhoto::query()
            ->where('public_id', $publicId)
            ->whereHas('complaint', fn (Builder $complaint) => $complaint->where('user_id', $customerId))
            ->first();
    }

    // ── Shop side ─────────────────────────────────────────────────────────────

    /** A complaint only when it belongs to this shop (and branch, for staff). */
    public function findForShop(string $publicId, int $shopId, ?string $branch = null): ?OrderComplaint
    {
        return $this->scopedToShop($shopId, $branch)
            ->with(['order:id,order_number,customer_name', 'customer:id,name', 'photos'])
            ->where('public_id', $publicId)
            ->first();
    }

    public function findPhotoForShop(string $publicId, int $shopId, ?string $branch = null): ?OrderComplaintPhoto
    {
        return OrderComplaintPhoto::query()
            ->where('public_id', $publicId)
            ->whereHas('complaint', fn (Builder $complaint) => $this->limitScope($complaint, $shopId, $branch))
            ->first();
    }

    public function respond(OrderComplaint $complaint, array $attributes): void
    {
        $complaint->update($attributes);
    }

    /** @return array{total: int, open: int, under_review: int, resolved: int} */
    public function statsForShop(int $shopId, ?string $branch = null): array
    {
        $counts = $this->scopedToShop($shopId, $branch)
            ->selectRaw('order_complaints.status as status, COUNT(*) as total')
            ->groupBy('order_complaints.status')
            ->pluck('total', 'status');

        return [
            'total' => (int) $counts->sum(),
            'open' => (int) ($counts['open'] ?? 0),
            'under_review' => (int) ($counts['under_review'] ?? 0),
            'resolved' => (int) ($counts['resolved'] ?? 0),
        ];
    }

    public function paginateForShop(int $shopId, array $filters, int $perPage, ?string $branch = null): LengthAwarePaginator
    {
        $column = self::SORTABLE[$filters['sort_by'] ?? 'created_at'] ?? self::SORTABLE['created_at'];
        $direction = ($filters['sort_direction'] ?? 'desc') === 'asc' ? 'asc' : 'desc';

        $query = OrderComplaint::query()
            ->join('shop_orders', 'shop_orders.id', '=', 'order_complaints.shop_order_id')
            ->where('order_complaints.shop_id', $shopId)
            ->whereNull('shop_orders.deleted_at')
            ->select('order_complaints.*', 'shop_orders.order_number', 'shop_orders.customer_name')
            ->with('photos')
            ->when($filters['status'] ?? null, fn ($query, $status) => $query->where('order_complaints.status', $status))
            ->when($filters['type'] ?? null, fn ($query, $type) => $query->where('order_complaints.type', $type))
            ->when(filled($filters['search'] ?? null), function ($query) use ($filters) {
                $like = '%'.addcslashes((string) $filters['search'], '%_\\').'%';

                $query->where(fn ($inner) => $inner
                    ->where('shop_orders.order_number', 'like', $like)
                    ->orWhere('shop_orders.customer_name', 'like', $like)
                    ->orWhere('order_complaints.description', 'like', $like));
            });

        if ($branch !== null) {
            $query->where(fn ($inner) => $inner
                ->where('shop_orders.branch_name', $branch)
                ->orWhereNull('shop_orders.branch_name')
                ->orWhere('shop_orders.branch_name', ''));
        }

        return $query
            ->orderBy($column, $direction)
            ->orderByDesc('order_complaints.id')
            ->paginate($perPage)
            ->withQueryString();
    }

    private function scopedToShop(int $shopId, ?string $branch): Builder
    {
        return $this->limitScope(OrderComplaint::query(), $shopId, $branch);
    }

    /** The same branch rule as the orders list: the branch's orders plus orders with no branch. */
    private function limitScope(Builder $query, int $shopId, ?string $branch): Builder
    {
        $query->where('order_complaints.shop_id', $shopId);

        // Complaints on trashed orders are hidden everywhere (list, stats, respond, photos).
        return $query->whereHas('order', function (Builder $order) use ($branch) {
            if ($branch !== null) {
                $order->where(fn (Builder $inner) => $inner
                    ->where('branch_name', $branch)
                    ->orWhereNull('branch_name')
                    ->orWhere('branch_name', ''));
            }
        });
    }
}
