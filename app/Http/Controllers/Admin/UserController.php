<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\User\BulkUserRequest;
use App\Models\User;
use App\Services\UserManagementService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class UserController extends Controller
{
    public function __construct(
        private readonly UserManagementService $userManagementService,
    ) {}

    // ── Index ─────────────────────────────────────────────────────────────────

    public function index(Request $request)
    {
        $filters = $request->only(['search', 'role', 'verified']);

        return Inertia::render('admin/users/Index', [
            'users'   => $this->userManagementService->getPaginated($filters),
            'stats'   => $this->userManagementService->getStats(),
            'filters' => $filters,
        ]);
    }

    // ── Show ──────────────────────────────────────────────────────────────────

    public function show(User $user)
    {
        return Inertia::render('admin/Users/Show', [
            'user' => $this->userManagementService->find($user->id),
        ]);
    }

    // ── Archive single ────────────────────────────────────────────────────────

    public function destroy(User $user)
    {
        $this->userManagementService->archive($user);

        return back()->with('toast', [
            'type'    => 'success',
            'message' => "{$user->name} has been archived.",
        ]);
    }

    // ── Bulk archive ──────────────────────────────────────────────────────────

    public function bulkArchive(BulkUserRequest $request)
    {
        $this->userManagementService->bulkArchive($request->validated('ids'));

        return back()->with('toast', [
            'type'    => 'success',
            'message' => count($request->validated('ids')) . ' user(s) archived.',
        ]);
    }

    // ── Archive index ─────────────────────────────────────────────────────────

    public function archiveIndex(Request $request)
    {
        $filters = $request->only(['search', 'role']);

        return Inertia::render('admin/Users/Archive', [
            'users'   => $this->userManagementService->getArchivedPaginated($filters),
            'total'   => $this->userManagementService->getArchivedTotal(),
            'filters' => $filters,
        ]);
    }

    // ── Restore ───────────────────────────────────────────────────────────────

    public function restore(int $id)
    {
        $this->userManagementService->restore($id);

        return back()->with('toast', [
            'type'    => 'success',
            'message' => 'User restored successfully.',
        ]);
    }

    // ── Bulk restore ──────────────────────────────────────────────────────────

    public function bulkRestore(BulkUserRequest $request)
    {
        $this->userManagementService->bulkRestore($request->validated('ids'));

        return back()->with('toast', [
            'type'    => 'success',
            'message' => count($request->validated('ids')) . ' user(s) restored.',
        ]);
    }

    // ── Force delete ──────────────────────────────────────────────────────────

    public function forceDelete(int $id)
    {
        $this->userManagementService->forceDelete($id);

        return back()->with('toast', [
            'type'    => 'success',
            'message' => 'User permanently deleted.',
        ]);
    }
}
