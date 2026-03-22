<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\LoginLog\BulkLoginLogRequest;
use App\Models\LoginLog;
use App\Services\LoginLogService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class LoginLogController extends Controller
{
    public function __construct(
        private readonly LoginLogService $loginLogService,
    ) {}

    // ── Index ─────────────────────────────────────────────────────────────────

    public function index(Request $request)
    {
        $filters = $request->only(['search', 'status', 'role', 'date']);

        return Inertia::render('admin/logs/Index', [
            'logs'    => $this->loginLogService->getPaginated($filters),
            'stats'   => $this->loginLogService->getStats(),
            'filters' => $filters,
        ]);
    }

    // ── Archive single ────────────────────────────────────────────────────────

    public function destroy(LoginLog $loginLog)
    {
        $this->loginLogService->archive($loginLog);

        return back()->with('toast', [
            'type'    => 'success',
            'message' => 'Log archived.',
        ]);
    }

    // ── Bulk archive ──────────────────────────────────────────────────────────

    public function bulkArchive(BulkLoginLogRequest $request)
    {
        $this->loginLogService->bulkArchive($request->validated('ids'));

        return back()->with('toast', [
            'type'    => 'success',
            'message' => count($request->validated('ids')) . ' log(s) archived.',
        ]);
    }

    // ── Archive index ─────────────────────────────────────────────────────────

    public function archiveIndex(Request $request)
    {
        $filters = $request->only(['search', 'status']);

        return Inertia::render('admin/logs/Archive', [
            'logs'    => $this->loginLogService->getArchivedPaginated($filters),
            'total'   => $this->loginLogService->getArchivedTotal(),
            'filters' => $filters,
        ]);
    }

    // ── Restore ───────────────────────────────────────────────────────────────

    public function restore(int $id)
    {
        $this->loginLogService->restore($id);

        return back()->with('toast', [
            'type'    => 'success',
            'message' => 'Log restored.',
        ]);
    }

    // ── Bulk restore ──────────────────────────────────────────────────────────

    public function bulkRestore(BulkLoginLogRequest $request)
    {
        $this->loginLogService->bulkRestore($request->validated('ids'));

        return back()->with('toast', [
            'type'    => 'success',
            'message' => count($request->validated('ids')) . ' log(s) restored.',
        ]);
    }

    // ── Force delete ──────────────────────────────────────────────────────────

    public function forceDelete(int $id)
    {
        $this->loginLogService->forceDelete($id);

        return back()->with('toast', [
            'type'    => 'success',
            'message' => 'Log permanently deleted.',
        ]);
    }
}
