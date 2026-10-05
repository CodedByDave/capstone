<?php

namespace App\Http\Controllers\Admin;

use App\Exceptions\InvalidAuditLogCsvException;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\AuditLogEntryRequest;
use App\Http\Requests\Admin\AuditLogFilterRequest;
use App\Http\Requests\Admin\BulkAuditLogRequest;
use App\Http\Requests\Admin\ImportAuditLogsRequest;
use App\Services\AuditLogService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

class LoginLogController extends Controller
{
    public function __construct(
        private readonly AuditLogService $auditLogService,
    ) {}

    public function index(AuditLogFilterRequest $request): Response
    {
        return Inertia::render(
            'admin/logs/Index',
            $this->auditLogService->getIndexData($request->filters()),
        );
    }

    public function exportCsv(AuditLogFilterRequest $request): StreamedResponse
    {
        return response()->streamDownload(
            fn () => $this->auditLogService->writeCsvExport($request->filters()),
            'audit-logs-'.now()->format('Y-m-d-His').'.csv',
            ['Content-Type' => 'text/csv; charset=UTF-8'],
        );
    }

    public function importCsv(ImportAuditLogsRequest $request): RedirectResponse
    {
        try {
            $result = $this->auditLogService->importCsv($request->uploadedFile());
        } catch (InvalidAuditLogCsvException $exception) {
            throw ValidationException::withMessages(['file' => $exception->getMessage()]);
        }

        return back()->with('toast', [
            'type' => 'success',
            'message' => "Audit backup restored: {$result['created']} created and {$result['skipped']} duplicate(s) skipped.",
        ]);
    }

    public function show(string $category, int $id): Response
    {
        return Inertia::render('admin/logs/Show', [
            'log' => $this->auditLogService->find($category, $id),
        ]);
    }

    public function destroy(AuditLogEntryRequest $request, string $category, int $id): RedirectResponse
    {
        $this->auditLogService->archive($category, $id);

        return back()->with('toast', [
            'type' => 'success',
            'message' => 'Log archived.',
        ]);
    }

    public function bulkArchive(BulkAuditLogRequest $request): RedirectResponse
    {
        $entries = $request->entries();
        $this->auditLogService->bulkArchive($entries);

        return back()->with('toast', [
            'type' => 'success',
            'message' => count($entries).' log(s) archived.',
        ]);
    }

    public function archiveIndex(AuditLogFilterRequest $request): Response
    {
        return Inertia::render(
            'admin/logs/Archive',
            $this->auditLogService->getArchiveData($request->filters()),
        );
    }

    public function restore(AuditLogEntryRequest $request, string $category, int $id): RedirectResponse
    {
        $this->auditLogService->restore($category, $id);

        return back()->with('toast', [
            'type' => 'success',
            'message' => 'Log restored.',
        ]);
    }

    public function bulkRestore(BulkAuditLogRequest $request): RedirectResponse
    {
        $entries = $request->entries();
        $this->auditLogService->bulkRestore($entries);

        return back()->with('toast', [
            'type' => 'success',
            'message' => count($entries).' log(s) restored.',
        ]);
    }

    public function forceDelete(AuditLogEntryRequest $request, string $category, int $id): RedirectResponse
    {
        $this->auditLogService->forceDelete($category, $id);

        return back()->with('toast', [
            'type' => 'success',
            'message' => 'Log permanently deleted.',
        ]);
    }
}
