<?php

namespace App\Http\Controllers\Shop\Inventory;

use App\Http\Controllers\Controller;
use App\Http\Requests\Shop\Inventory\StoreInventoryRequest;
use App\Http\Requests\Shop\Inventory\UpdateInventoryRequest;
use App\Http\Requests\Shop\Inventory\AdjustStockRequest;
use App\Models\Inventory;
use App\Models\Shop;
use App\Models\Employee;
use App\Services\InventoryService;
use App\Services\InventoryCategoryService;
use App\Services\SupplierService;
use Inertia\Inertia;

class InventoryController extends Controller
{
    public function __construct(
        private readonly InventoryService         $inventoryService,
        private readonly InventoryCategoryService $categoryService,
        private readonly SupplierService          $supplierService,
    ) {}

    private function getShop(): Shop
    {
        $user = auth()->user();

        if ($user->role === 'owner') {
            return Shop::where('owner_id', $user->id)->firstOrFail();
        }

        // Staff: get shop via employee record
        $employee = Employee::where('user_id', $user->id)->firstOrFail();
        return Shop::findOrFail($employee->shop_id);
    }
    public function index()
    {
        $shop    = $this->getShop();
        $filters = request()->only(['search', 'category_id', 'supplier_id', 'status', 'stock']);

        return Inertia::render('shop/inventory/Index', [
            'inventory'  => $this->inventoryService->paginate($shop->id, perPage: 15, filters: $filters),
            'categories' => $this->categoryService->all($shop->id),
            'suppliers'  => $this->supplierService->allActive($shop->id),
            'filters'    => $filters,
            'lowStock'   => $this->inventoryService->lowStock($shop->id),
        ]);
    }

    public function create()
    {
        $shop = $this->getShop();

        return Inertia::render('shop/inventory/Create', [
            'categories' => $this->categoryService->all($shop->id),
            'suppliers'  => $this->supplierService->allActive($shop->id),
        ]);
    }

    public function store(StoreInventoryRequest $request)
    {
        $shop = $this->getShop();

        $this->inventoryService->create($shop->id, $request->validated());

        $route = auth()->user()->role === 'owner' ? 'inventory.index' : 'staff.inventory.index';

        return redirect()->route($route)
            ->with('toast', ['type' => 'success', 'message' => 'Item added to inventory.']);
    }

    public function show(Inventory $inventory)
    {
        $shop = $this->getShop();

        $item = $this->inventoryService->find($inventory->id, $shop->id);

        abort_if(!$item, 404);

        return Inertia::render('shop/inventory/Show', [
            'inventory' => $item,
        ]);
    }

    public function edit(Inventory $inventory)
    {
        $shop = $this->getShop();

        $item = $this->inventoryService->find($inventory->id, $shop->id);

        abort_if(!$item, 404);

        return Inertia::render('shop/inventory/Edit', [
            'inventory'  => $item,
            'categories' => $this->categoryService->all($shop->id),
            'suppliers'  => $this->supplierService->allActive($shop->id),
        ]);
    }

    public function update(UpdateInventoryRequest $request, Inventory $inventory)
    {
        $shop = $this->getShop();
        $item = $this->inventoryService->find($inventory->id, $shop->id);

        abort_if(!$item, 404);

        $this->inventoryService->update($item, $request->validated());

        return redirect()->route('inventory.show', $inventory->id)
            ->with('toast', ['type' => 'success', 'message' => 'Inventory item updated.']);
    }

    public function destroy(Inventory $inventory)
    {
        $shop = $this->getShop();
        $item = $this->inventoryService->find($inventory->id, $shop->id);

        abort_if(!$item, 404);

        $this->inventoryService->delete($item);

        return redirect()->route('inventory.index')
            ->with('toast', ['type' => 'success', 'message' => 'Item archived successfully.']);
    }

    public function adjustStock(AdjustStockRequest $request, Inventory $inventory)
    {
        $shop = $this->getShop();
        $item = $this->inventoryService->find($inventory->id, $shop->id);

        abort_if(!$item, 404);

        $validated = $request->validated();

        try {
            $this->inventoryService->adjustStock(
                inventory: $item,
                type: $validated['type'],
                quantity: $validated['quantity'],
                referenceNumber: $validated['reference_number'] ?? null,
                notes: $validated['notes'] ?? null,
            );
        } catch (\InvalidArgumentException $e) {
            return back()->withErrors(['quantity' => $e->getMessage()]);
        }

        return back()->with('toast', ['type' => 'success', 'message' => 'Stock adjusted successfully.']);
    }
}
