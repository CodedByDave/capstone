<?php

namespace App\Http\Controllers\Shop\Employee;

use App\Http\Controllers\Controller;
use App\Http\Requests\Shop\Employee\StoreBranchRequest;
use App\Http\Requests\Shop\Employee\UpdateBranchRequest;
use App\Models\Branch;
use App\Models\Employee;
use App\Models\Shop;
use App\Services\BranchService;
use App\Traits\BranchScoped;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class BranchController extends Controller
{
    use BranchScoped;

    public function __construct(protected BranchService $service) {}

    private function getShop(): Shop
    {
        $user = Auth::user();

        if ($user->role === 'owner') {
            return Shop::where('owner_id', $user->id)->firstOrFail();
        }

        $employee = Employee::where('user_id', $user->id)->firstOrFail();
        return Shop::findOrFail($employee->shop_id);
    }

    private function getShopId(): int
    {
        return $this->getShop()->id;
    }

    private function indexRouteName(): string
    {
        return Auth::user()->role === 'owner' ? 'branch.index' : 'staff.branch.index';
    }

    public function index(): Response
    {
        $shop   = $this->getShop();
        $branch = $this->getStaffBranch();

        return Inertia::render('shop/employee/branch/Index',
            $this->service->indexData($shop->id, $branch)
        );
    }

    public function create(): Response
    {
        return Inertia::render('shop/employee/branch/Create');
    }

    public function store(StoreBranchRequest $request): RedirectResponse
    {
        $this->service->create($request->validated(), $this->getShopId());

        return redirect()
            ->route($this->indexRouteName())
            ->with('toast', ['type' => 'success', 'message' => 'Branch created successfully.']);
    }

    public function edit(Branch $branch): Response
    {
        abort_if($branch->shop_id !== $this->getShopId(), 403);

        return Inertia::render('shop/employee/branch/Edit', compact('branch'));
    }

    public function update(UpdateBranchRequest $request, Branch $branch): RedirectResponse
    {
        abort_if($branch->shop_id !== $this->getShopId(), 403);
        $this->service->update($branch, $request->validated());

        return redirect()
            ->route($this->indexRouteName())
            ->with('toast', ['type' => 'success', 'message' => 'Branch updated successfully.']);
    }

    public function destroy(Branch $branch): RedirectResponse
    {
        abort_if($branch->shop_id !== $this->getShopId(), 403);
        $this->service->archive($branch);

        return redirect()
            ->route($this->indexRouteName())
            ->with('toast', ['type' => 'success', 'message' => 'Branch archived successfully.']);
    }

    public function archive(): Response
    {
        return Inertia::render('shop/employee/branch/Archive', [
            'branches' => $this->service->archiveData($this->getShopId()),
        ]);
    }

    public function restore(int $id): RedirectResponse
    {
        $this->service->restore($id, $this->getShopId());

        return redirect()
            ->route($this->indexRouteName())
            ->with('toast', ['type' => 'success', 'message' => 'Branch restored successfully.']);
    }
}
