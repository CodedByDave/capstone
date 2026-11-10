<?php

namespace App\Http\Controllers\SupplierPortal;

use App\Http\Controllers\Controller;
use App\Http\Middleware\EnsureActiveSupplier;
use App\Http\Requests\SupplierPortal\SupplierItemRequest;
use App\Services\SupplierPortalService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class SupplierItemController extends Controller
{
    public function __construct(private readonly SupplierPortalService $portal) {}

    public function store(SupplierItemRequest $request): RedirectResponse
    {
        $this->portal->createItem($this->supplier($request), $request->validated());

        return back()->with('toast', ['type' => 'success', 'message' => 'Supply added.']);
    }

    public function update(SupplierItemRequest $request, string $item): RedirectResponse
    {
        $this->portal->updateItem($this->supplier($request), $item, $request->validated());

        return back()->with('toast', ['type' => 'success', 'message' => 'Supply updated.']);
    }

    public function destroy(Request $request, string $item): RedirectResponse
    {
        $this->portal->deleteItem($this->supplier($request), $item);

        return back()->with('toast', ['type' => 'success', 'message' => 'Supply removed.']);
    }

    private function supplier(Request $request)
    {
        return $request->attributes->get(EnsureActiveSupplier::ATTRIBUTE);
    }
}
