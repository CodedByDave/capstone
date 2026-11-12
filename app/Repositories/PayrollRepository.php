<?php

namespace App\Repositories;

use App\DTOs\TableSort;
use App\Models\Payroll;
use App\Models\Shop;
use App\Models\User;
use App\Repositories\Concerns\AppliesTableSort;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class PayrollRepository
{
    use AppliesTableSort;

    public function paginate(Shop $shop, int $perPage = 10, ?TableSort $sort = null, bool $archived = false): LengthAwarePaginator
    {
        $query = Payroll::with(['creator:id,name'])
            ->withCount('items')
            ->withSum('items', 'net_pay')
            ->when($archived, fn ($q) => $q->onlyTrashed())
            ->where('shop_id', $shop->id);

        $sorted = $this->applyTableSort($query, $sort, [
            'period_label' => 'period_start',
            'period_range' => 'period_start',
            'items_count' => 'items_count',
            'items_sum_net_pay' => 'items_sum_net_pay',
            'status' => 'status',
            'creator' => fn ($q, $dir) => $q->orderBy(
                User::query()
                    ->select('name')
                    ->whereColumn('users.id', 'payrolls.created_by'),
                $dir,
            ),
        ]);

        if (! $sorted) {
            $query->latest('period_start');
        }

        return $query->paginate($perPage)->withQueryString();
    }

    /**
     * Whether the shop already has a payroll for the period. Archived ones count too, because the
     * period stays reserved until the archived payroll is restored.
     *
     * @return 'active'|'archived'|null
     */
    public function periodState(int $shopId, string $periodStart, string $periodEnd): ?string
    {
        $payroll = Payroll::withTrashed()
            ->where('shop_id', $shopId)
            ->whereDate('period_start', $periodStart)
            ->whereDate('period_end', $periodEnd)
            ->first(['id', 'deleted_at']);

        return match (true) {
            $payroll === null => null,
            $payroll->trashed() => 'archived',
            default => 'active',
        };
    }

    /** An archived payroll of the shop; another shop's payroll is reported as missing. */
    public function findArchivedForShop(string $publicId, int $shopId): Payroll
    {
        return Payroll::onlyTrashed()
            ->where('shop_id', $shopId)
            ->where('public_id', $publicId)
            ->firstOrFail();
    }

    public function restore(Payroll $payroll): void
    {
        $payroll->restore();
    }

    public function find(int $id, Shop $shop): Payroll
    {
        return Payroll::with(['items.employee:id,employee_id,first_name,last_name,position,pay_rate,pay_basis', 'creator:id,name'])
            ->where('shop_id', $shop->id)
            ->findOrFail($id);
    }

    public function create(array $data): Payroll
    {
        return Payroll::create($data);
    }

    public function update(Payroll $payroll, array $data): Payroll
    {
        $payroll->update($data);

        return $payroll->fresh();
    }

    public function delete(Payroll $payroll): void
    {
        $payroll->delete();
    }
}
