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

    public function paginate(Shop $shop, int $perPage = 10, ?TableSort $sort = null): LengthAwarePaginator
    {
        $query = Payroll::with(['creator:id,name'])
            ->withCount('items')
            ->withSum('items', 'net_pay')
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

        return $query->paginate($perPage);
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
