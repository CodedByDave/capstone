<?php

namespace App\Http\Controllers\Shop\Inventory;

use App\Exceptions\SupplierAccountException;
use App\Http\Controllers\Controller;
use App\Http\Requests\Shop\Inventory\StoreSupplierRequest;
use App\Http\Requests\Shop\Inventory\UpdateSupplierRequest;
use App\Http\Requests\TableSortRequest;
use App\Services\SupplierPriceListService;
use App\Services\SupplierService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;

class SupplierController extends Controller
{
    public function __construct(
        private readonly SupplierService $supplierService,
        private readonly SupplierPriceListService $priceLists,
    ) {}

    private function shopId(): int
    {
        return $this->supplierService->shopIdFor(auth()->user());
    }

    private function indexRoute(): string
    {
        return auth()->user()->role === 'owner' ? 'supplier.index' : 'staff.supplier.index';
    }

    public function index(TableSortRequest $request)
    {
        return Inertia::render('shop/inventory/supplier/Index', [
            'suppliers' => $this->supplierService->paginate($this->shopId(), $request->sort()),
        ]);
    }

    public function create()
    {
        return Inertia::render('shop/inventory/supplier/Create');
    }

    public function store(StoreSupplierRequest $request)
    {
        try {
            $this->supplierService->create($this->shopId(), $request->validated());
        } catch (SupplierAccountException $exception) {
            return back()->withInput()->withErrors(['email' => $exception->getMessage()]);
        }

        return redirect()->route($this->indexRoute())
            ->with('toast', ['type' => 'success', 'message' => 'Supplier added. A login invitation was emailed to them.']);
    }

    public function show(string $supplier)
    {
        return Inertia::render('shop/inventory/supplier/Show', [
            'supplier' => $this->priceLists->supplierDetailForShop(
                $supplier,
                $this->shopId(),
                forOwner: auth()->user()->role === 'owner',
            ),
        ]);
    }

    public function edit(string $supplier)
    {
        return Inertia::render('shop/inventory/supplier/Edit', [
            'supplier' => $this->supplierService->find($supplier, $this->shopId()),
        ]);
    }

    public function update(UpdateSupplierRequest $request, string $supplier)
    {
        $record = $this->supplierService->find($supplier, $this->shopId());

        try {
            $this->supplierService->update($record, $request->validated());
        } catch (SupplierAccountException $exception) {
            return back()->withInput()->withErrors(['email' => $exception->getMessage()]);
        }

        return redirect()->route($this->indexRoute())
            ->with('toast', ['type' => 'success', 'message' => 'Supplier updated successfully.']);
    }

    public function invite(Request $request, string $supplier)
    {
        $record = $this->supplierService->find($supplier, $this->shopId());

        try {
            $this->supplierService->inviteAccount($record);
        } catch (SupplierAccountException $exception) {
            return $request->wantsJson()
                ? response()->json(['message' => $exception->getMessage()], 422)
                : back()->with('toast', ['type' => 'error', 'message' => $exception->getMessage()]);
        }

        return $request->wantsJson()
            ? response()->json(['success' => true])
            : back()->with('toast', ['type' => 'success', 'message' => 'Login invitation sent.']);
    }

    public function priceListPhoto(string $supplier, string $priceList)
    {
        $path = $this->priceLists->photoPathForShop($supplier, $priceList, $this->shopId());

        return Storage::disk(SupplierPriceListService::DISK)->response($path);
    }

    public function destroy(string $supplier)
    {
        $this->supplierService->delete($this->supplierService->find($supplier, $this->shopId()));

        if (request()->wantsJson()) {
            return response()->json(['success' => true]);
        }

        return redirect()->route($this->indexRoute())
            ->with('toast', ['type' => 'success', 'message' => 'Supplier archived successfully.']);
    }

    public function archive(TableSortRequest $request)
    {
        return Inertia::render('shop/inventory/supplier/Archive', [
            'archived' => $this->supplierService->archived($this->shopId(), $request->sort()),
        ]);
    }

    public function restore(string $id)
    {
        $this->supplierService->restore($id, $this->shopId());

        if (request()->wantsJson()) {
            return response()->json(['success' => true]);
        }

        return redirect()->route($this->indexRoute())
            ->with('toast', ['type' => 'success', 'message' => 'Supplier restored successfully.']);
    }
}
