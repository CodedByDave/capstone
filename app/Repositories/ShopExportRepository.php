<?php

namespace App\Repositories;

use App\Models\Attendance;
use App\Models\Employee;
use App\Models\Expense;
use App\Models\Inventory;
use App\Models\InventoryMovement;
use App\Models\PayrollItem;
use App\Models\ShopOrder;
use App\Models\ShopOrderPayment;
use App\Models\Supplier;
use Illuminate\Support\LazyCollection;

/** Streams every record of one shop, a chunk at a time, for CSV exports. */
class ShopExportRepository
{
    private const CHUNK = 500;

    public function orders(int $shopId): LazyCollection
    {
        return ShopOrder::query()
            ->withTrashed()
            ->forShop($shopId)
            ->with('service:id,service_name')
            ->lazyById(self::CHUNK);
    }

    public function income(int $shopId): LazyCollection
    {
        return ShopOrderPayment::query()
            ->where('shop_id', $shopId)
            ->confirmed()
            ->whereHas('order')
            ->with('order:id,order_number,customer_name')
            ->lazyById(self::CHUNK);
    }

    public function expenses(int $shopId): LazyCollection
    {
        return Expense::query()
            ->withTrashed()
            ->where('shop_id', $shopId)
            ->with('category:id,name')
            ->lazyById(self::CHUNK);
    }

    public function inventory(int $shopId): LazyCollection
    {
        return Inventory::query()
            ->forShop($shopId)
            ->with('category:id,name', 'supplier:id,name')
            ->lazyById(self::CHUNK);
    }

    public function stockMovements(int $shopId): LazyCollection
    {
        return InventoryMovement::query()
            ->forShop($shopId)
            ->with('inventory:id,name,sku')
            ->lazyById(self::CHUNK);
    }

    public function suppliers(int $shopId): LazyCollection
    {
        return Supplier::query()
            ->withTrashed()
            ->where('shop_id', $shopId)
            ->lazyById(self::CHUNK);
    }

    public function employees(int $shopId): LazyCollection
    {
        return Employee::query()
            ->withTrashed()
            ->where('shop_id', $shopId)
            ->lazyById(self::CHUNK);
    }

    public function attendance(int $shopId): LazyCollection
    {
        return Attendance::query()
            ->where('shop_id', $shopId)
            ->with('employee:id,first_name,last_name,employee_id')
            ->lazyById(self::CHUNK);
    }

    public function payroll(int $shopId): LazyCollection
    {
        return PayrollItem::query()
            ->whereHas('payroll', fn ($q) => $q->where('shop_id', $shopId))
            ->with('payroll:id,period_label,period_start,period_end,status', 'employee:id,first_name,last_name,employee_id')
            ->lazyById(self::CHUNK);
    }
}
