<?php

namespace App\Http\Controllers\Shop\Inventory;

use App\Http\Controllers\Controller;
use App\Http\Requests\Shop\Inventory\StoreInventoryCategoryRequest;
use App\Http\Requests\Shop\Inventory\UpdateInventoryCategoryRequest;
use App\Models\InventoryCategory;
use App\Models\Shop;
use App\Services\InventoryCategoryService;
use Inertia\Inertia;

class InventoryCategoryController extends Controller
{
    public function __construct(
        private readonly InventoryCategoryService $categoryService,
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

        return Inertia::render('shop/inventory/category/Index', [
            'categories' => $this->categoryService->all($shop->id),
        ]);
    }

    public function create()
    {
        return Inertia::render('shop/inventory/category/Create');
    }

    /**
     * Always returns JSON — called via axios from the inline category table.
     */
    public function store(StoreInventoryCategoryRequest $request)
    {
        $shop     = $this->getShop();
        $category = $this->categoryService->create($shop->id, $request->validated());

        return response()->json([
            'id'          => $category->id,
            'name'        => $category->name,
            'description' => $category->description,
        ], 201);
    }

    public function edit(InventoryCategory $inventoryCategory)
    {
        return Inertia::render('shop/inventory/category/Edit', [
            'category' => $inventoryCategory,
        ]);
    }

    /**
     * Always returns JSON — called via axios from the inline category table.
     */
    public function update(UpdateInventoryCategoryRequest $request, InventoryCategory $inventoryCategory)
    {
        $this->categoryService->update($inventoryCategory, $request->validated());

        return response()->json([
            'id'          => $inventoryCategory->id,
            'name'        => $inventoryCategory->name,
            'description' => $inventoryCategory->description,
        ]);
    }

    /**
     * Always returns JSON — called via axios from the inline category table.
     */
    public function destroy(InventoryCategory $inventoryCategory)
    {
        $this->categoryService->delete($inventoryCategory);

        return response()->json(['success' => true]);
    }

    public function archive()
    {
        $shop = $this->getShop();
        return Inertia::render('shop/inventory/category/Archive', [
            'archived' => $this->categoryService->archived($shop->id),
        ]);
    }

    public function restore(int $id)
    {
        $this->categoryService->restore($id);
        return response()->json(['success' => true]);
    }
}
