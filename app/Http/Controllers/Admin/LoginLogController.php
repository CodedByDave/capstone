<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\AuditLogService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class LoginLogController extends Controller
{
    public function __construct(
        private readonly AuditLogService $auditLogService,
    ) {}

    // ── Index ─────────────────────────────────────────────────────────────────

    public function index(Request $request)
    {
        $filters = $request->only([
            'search',
            'category',
            'role',
            'module',
            'event',
            'date',
            'sort_by',
            'sort_direction',
        ]);
        $perPage = min(max($request->integer('per_page', 20), 5), 100);
        $filters['per_page'] = (string) $perPage;

        return Inertia::render('admin/logs/Index', [
            'logs' => $this->auditLogService->getPaginated($filters, $perPage),
            'stats' => $this->auditLogService->getStats(),
            'modules' => $this->auditLogService->getModules(),
            'filters' => $filters,
        ]);
    }

    public function exportCsv(Request $request)
    {
        $filters = $request->only([
            'search', 'category', 'role', 'module', 'event', 'date',
        ]);
        $logs = $this->auditLogService->getForCsvExport($filters);

        return response()->streamDownload(function () use ($logs) {
            $output = fopen('php://output', 'w');
            fwrite($output, "\xEF\xBB\xBF");
            fputcsv($output, [
                'type', 'user', 'email', 'role', 'module', 'event',
                'shop', 'ip_address', 'user_agent', 'details', 'occurred_at',
            ], ',', '"', '');

            foreach ($logs as $log) {
                fputcsv($output, [
                    $log->category,
                    $log->name,
                    $log->email,
                    $log->role,
                    $log->module,
                    $log->event,
                    $log->shop_name,
                    $log->ip_address,
                    $log->user_agent,
                    $log->details,
                    $log->occurred_at,
                ], ',', '"', '');
            }

            fclose($output);
        }, 'audit-logs-'.now()->format('Y-m-d-His').'.csv', [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    public function importCsv(Request $request)
    {
        $validated = $request->validate([
            'file' => ['required', 'file', 'mimes:csv,txt', 'max:10240'],
        ]);
        $result = $this->auditLogService->importCsv($validated['file']);

        return back()->with('toast', [
            'type' => 'success',
            'message' => "Audit backup restored: {$result['created']} created and {$result['skipped']} duplicate(s) skipped.",
        ]);
    }

    public function show(string $category, int $id)
    {
        return Inertia::render('admin/logs/Show', [
            'log' => $this->auditLogService->find($category, $id),
        ]);
    }

    // ── Archive single ────────────────────────────────────────────────────────

    public function destroy(string $category, int $id)
    {
        $this->auditLogService->archive($category, $id);

        return back()->with('toast', [
            'type' => 'success',
            'message' => 'Log archived.',
        ]);
    }

    // ── Bulk archive ──────────────────────────────────────────────────────────

    public function bulkArchive(Request $request)
    {
        $entries = $this->validatedEntries($request);
        $this->auditLogService->bulkArchive($entries);

        return back()->with('toast', [
            'type' => 'success',
            'message' => count($entries).' log(s) archived.',
        ]);
    }

    // ── Archive index ─────────────────────────────────────────────────────────

    public function archiveIndex(Request $request)
    {
        $filters = $request->only(['search', 'category', 'role', 'module', 'event', 'date']);
        $perPage = min(max($request->integer('per_page', 20), 5), 100);

        return Inertia::render('admin/logs/Archive', [
            'logs' => $this->auditLogService->getArchivedPaginated($filters, $perPage),
            'total' => $this->auditLogService->getStats()['archived'],
            'filters' => $filters,
        ]);
    }

    // ── Restore ───────────────────────────────────────────────────────────────

    public function restore(string $category, int $id)
    {
        $this->auditLogService->restore($category, $id);

        return back()->with('toast', [
            'type' => 'success',
            'message' => 'Log restored.',
        ]);
    }

    // ── Bulk restore ──────────────────────────────────────────────────────────

    public function bulkRestore(Request $request)
    {
        $entries = $this->validatedEntries($request);
        $this->auditLogService->bulkRestore($entries);

        return back()->with('toast', [
            'type' => 'success',
            'message' => count($entries).' log(s) restored.',
        ]);
    }

    // ── Force delete ──────────────────────────────────────────────────────────

    public function forceDelete(string $category, int $id)
    {
        $this->auditLogService->forceDelete($category, $id);

        return back()->with('toast', [
            'type' => 'success',
            'message' => 'Log permanently deleted.',
        ]);
    }

    private function validatedEntries(Request $request): array
    {
        return $request->validate([
            'entries' => ['required', 'array', 'min:1'],
            'entries.*.category' => ['required', 'string', 'in:authentication,activity'],
            'entries.*.id' => ['required', 'integer', 'min:1'],
        ])['entries'];
    }
}
