<?php

namespace App\Http\Controllers\Shop\Inventory;

use App\Http\Controllers\Controller;
use App\Http\Requests\Shop\Inventory\StoreSupplierRequest;
use App\Http\Requests\Shop\Inventory\UpdateSupplierRequest;
use App\Models\Shop;
use App\Models\Supplier;
use App\Services\SupplierService;
use Inertia\Inertia;

class SupplierController extends Controller
{
    public function __construct(
        private readonly SupplierService $supplierService,
    ) {}

    private function getShop(): Shop
    {
        $user = auth()->user();

        if ($user->role === 'owner') {
            return Shop::where('owner_id', $user->id)->firstOrFail();
        }

        // Staff: get shop via employee record
        $employee = \App\Models\Employee::where('user_id', $user->id)->firstOrFail();
        return Shop::findOrFail($employee->shop_id);
    }

    public function index()
    {
        $shop = $this->getShop();

        return Inertia::render('shop/inventory/supplier/Index', [
            'suppliers' => $this->supplierService->paginate($shop->id),
        ]);
    }

    public function create()
    {
        return Inertia::render('shop/inventory/supplier/Create');
    }

    public function store(StoreSupplierRequest $request)
    {
        $shop = $this->getShop();

        $this->supplierService->create($shop->id, $request->validated());

        return redirect()->route('supplier.index')
            ->with('toast', ['type' => 'success', 'message' => 'Supplier added successfully.']);
    }

    public function edit(Supplier $supplier)
    {
        return Inertia::render('shop/inventory/supplier/Edit', [
            'supplier' => $supplier,
        ]);
    }

    public function update(UpdateSupplierRequest $request, Supplier $supplier)
    {
        $this->supplierService->update($supplier, $request->validated());

        return redirect()->route('supplier.index')
            ->with('toast', ['type' => 'success', 'message' => 'Supplier updated successfully.']);
    }

    public function destroy(Supplier $supplier)
    {
        $this->supplierService->delete($supplier);

        if (request()->wantsJson()) {
            return response()->json(['success' => true]);
        }

        return redirect()->route('supplier.index')
            ->with('toast', ['type' => 'success', 'message' => 'Supplier archived successfully.']);
    }

    public function archive()
    {
        $shop = $this->getShop();
        return Inertia::render('shop/inventory/supplier/Archive', [
            'archived' => $this->supplierService->archived($shop->id),
        ]);
    }

    public function restore(int $id)
    {
        $this->supplierService->restore($id);

        if (request()->wantsJson()) {
            return response()->json(['success' => true]);
        }

        return redirect()->route('supplier.index')
            ->with('toast', ['type' => 'success', 'message' => 'Supplier restored successfully.']);
    }
}
