<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\BulkUserRequest;
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
        $filters = $request->only([
            'search',
            'role',
            'verified',
            'sort_by',
            'sort_direction',
        ]);
        $perPage = min(max($request->integer('per_page', 20), 5), 100);
        $filters['per_page'] = (string) $perPage;

        return Inertia::render('admin/users/Index', [
            'users' => $this->userManagementService->getPaginated($filters, $perPage),
            'stats' => $this->userManagementService->getStats(),
            'filters' => $filters,
        ]);
    }

    public function exportCsv()
    {
        $users = $this->userManagementService->getCsvExportUsers();

        return response()->streamDownload(function () use ($users) {
            $output = fopen('php://output', 'w');
            fwrite($output, "\xEF\xBB\xBF");
            fputcsv($output, ['name', 'email', 'role', 'verified', 'joined_at'], ',', '"', '');

            foreach ($users as $user) {
                fputcsv(
                    $output,
                    [
                        $user->name,
                        $user->email,
                        $user->role,
                        $user->email_verified_at !== null ? 'yes' : 'no',
                        $user->created_at?->toDateTimeString(),
                    ],
                    ',',
                    '"',
                    '',
                );
            }

            fclose($output);
        }, 'users-backup-'.now()->format('Y-m-d-His').'.csv', [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    public function importCsv(Request $request)
    {
        $validated = $request->validate([
            'file' => ['required', 'file', 'mimes:csv,txt', 'max:5120'],
        ]);

        $result = $this->userManagementService->importCsv($validated['file']);

        return back()->with('toast', [
            'type' => 'success',
            'message' => "CSV import complete: {$result['created']} created and {$result['updated']} updated.",
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
            'type' => 'success',
            'message' => "{$user->name} has been archived.",
        ]);
    }

    // ── Bulk archive ──────────────────────────────────────────────────────────

    public function bulkArchive(BulkUserRequest $request)
    {
        $this->userManagementService->bulkArchive($request->validated('ids'));

        return back()->with('toast', [
            'type' => 'success',
            'message' => count($request->validated('ids')).' user(s) archived.',
        ]);
    }

    // ── Archive index ─────────────────────────────────────────────────────────

    public function archiveIndex(Request $request)
    {
        $filters = $request->only(['search', 'role']);

        return Inertia::render('admin/users/Archive', [
            'users' => $this->userManagementService->getArchivedPaginated($filters),
            'total' => $this->userManagementService->getArchivedTotal(),
            'filters' => $filters,
        ]);
    }

    // ── Restore ───────────────────────────────────────────────────────────────

    public function restore(int $id)
    {
        $this->userManagementService->restore($id);

        return back()->with('toast', [
            'type' => 'success',
            'message' => 'User restored successfully.',
        ]);
    }

    // ── Bulk restore ──────────────────────────────────────────────────────────

    public function bulkRestore(BulkUserRequest $request)
    {
        $this->userManagementService->bulkRestore($request->validated('ids'));

        return back()->with('toast', [
            'type' => 'success',
            'message' => count($request->validated('ids')).' user(s) restored.',
        ]);
    }

    // ── Force delete ──────────────────────────────────────────────────────────

    public function forceDelete(int $id)
    {
        $this->userManagementService->forceDelete($id);

        return back()->with('toast', [
            'type' => 'success',
            'message' => 'User permanently deleted.',
        ]);
    }
}
