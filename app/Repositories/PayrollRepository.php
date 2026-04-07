<?php

namespace App\Repositories;

use App\Models\Payroll;
use App\Models\Shop;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class PayrollRepository
{
    public function paginate(Shop $shop, int $perPage = 10): LengthAwarePaginator
    {
        return Payroll::with(['creator:id,name'])
            ->withCount('items')
            ->withSum('items', 'net_pay')
            ->where('shop_id', $shop->id)
            ->latest('period_start')
            ->paginate($perPage);
    }

    public function find(int $id, Shop $shop): Payroll
    {
        return Payroll::with(['items.employee:id,employee_id,first_name,last_name,position,branch_name,salary', 'creator:id,name'])
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
