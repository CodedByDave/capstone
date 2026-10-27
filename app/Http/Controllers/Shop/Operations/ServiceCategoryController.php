<?php

namespace App\Http\Controllers\Shop\Operations;

use App\Http\Controllers\Controller;
use App\Http\Requests\Shop\Operations\StoreServiceCategoryRequest;
use App\Services\ServiceCategoryService;
use App\Services\ShopContext;
use Illuminate\Http\RedirectResponse;

class ServiceCategoryController extends Controller
{
    public function __construct(
        private readonly ServiceCategoryService $categories,
        private readonly ShopContext $shopContext,
    ) {}

    public function store(StoreServiceCategoryRequest $request): RedirectResponse
    {
        $category = $this->categories->create(
            $this->shopContext->shopIdFor($request->user()),
            $request->validated(),
        );

        return back()->with('toast', [
            'type' => 'success',
            'message' => "Category \"{$category->name}\" added.",
        ]);
    }

    public function destroy(string $key): RedirectResponse
    {
        $this->categories->delete($this->shopContext->shopIdFor(auth()->user()), $key);

        return back()->with('toast', [
            'type' => 'success',
            'message' => 'Category removed.',
        ]);
    }
}
