<?php

namespace App\Http\Controllers\Shop\Employee;

use App\Http\Controllers\Controller;
use App\Http\Requests\Shop\StoreBranchRequest;
use App\Http\Requests\Shop\UpdateBranchRequest;
use App\Models\Branch;
use App\Services\BranchService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class BranchController extends Controller
{
    public function __construct(protected BranchService $service) {}

    public function index(): Response
    {
        return Inertia::render('shop/employee/branch/Index',
            $this->service->indexData(Auth::user()->shop->id)
        );
    }

    public function create(): Response
    {
        return Inertia::render('shop/employee/branch/Create');
    }

    public function store(StoreBranchRequest $request): RedirectResponse
    {
        $this->service->create($request->validated(), Auth::user()->shop->id);

        return redirect()
            ->route('branch.index')
            ->with('toast', ['type' => 'success', 'message' => 'Branch created successfully.']);
    }

    public function edit(Branch $branch): Response
    {
        abort_if($branch->shop_id !== Auth::user()->shop->id, 403);

        return Inertia::render('shop/employee/branch/Edit', compact('branch'));
    }

    public function update(UpdateBranchRequest $request, Branch $branch): RedirectResponse
    {
        $this->service->update($branch, $request->validated());

        return redirect()
            ->route('branch.index')
            ->with('toast', ['type' => 'success', 'message' => 'Branch updated successfully.']);
    }

    public function destroy(Branch $branch): RedirectResponse
    {
        $this->service->archive($branch);

        return redirect()
            ->route('branch.index')
            ->with('toast', ['type' => 'success', 'message' => 'Branch archived successfully.']);
    }

    public function archive(): Response
    {
        return Inertia::render('shop/employee/branch/Archive', [
            'branches' => $this->service->archiveData(Auth::user()->shop->id),
        ]);
    }

    public function restore(int $id): RedirectResponse
    {
        $this->service->restore($id);

        return redirect()
            ->route('branch.index')
            ->with('toast', ['type' => 'success', 'message' => 'Branch restored successfully.']);
    }
}
