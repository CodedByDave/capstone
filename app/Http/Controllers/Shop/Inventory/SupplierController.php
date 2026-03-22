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
        return Shop::where('owner_id', auth()->id())->firstOrFail();
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

        return redirect()->route('supplier.index')
            ->with('toast', ['type' => 'success', 'message' => 'Supplier deleted successfully.']);
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
        return redirect()->route('supplier.index')
            ->with('toast', ['type' => 'success', 'message' => 'Supplier restored successfully.']);
    }
}
