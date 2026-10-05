<?php

namespace App\Http\Controllers\Admin;

use App\Exceptions\InvalidIssueReportCsvException;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ImportIssueReportsRequest;
use App\Http\Requests\Admin\IssueReportFilterRequest;
use App\Http\Requests\Admin\UpdateIssueReportRequest;
use App\Models\IssueReport;
use App\Services\IssueReportService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

class IssueReportController extends Controller
{
    public function __construct(
        private readonly IssueReportService $issueReportService,
    ) {}

    public function index(IssueReportFilterRequest $request): Response
    {
        return Inertia::render(
            'admin/issues/Index',
            $this->issueReportService->getIndexData($request->filters()),
        );
    }

    public function exportCsv(IssueReportFilterRequest $request): StreamedResponse
    {
        return response()->streamDownload(
            fn () => $this->issueReportService->writeCsvExport($request->filters()),
            'issue-reports-'.now()->format('Y-m-d-His').'.csv',
            ['Content-Type' => 'text/csv; charset=UTF-8'],
        );
    }

    public function importCsv(ImportIssueReportsRequest $request): RedirectResponse
    {
        try {
            $result = $this->issueReportService->importCsv($request->uploadedFile());
        } catch (InvalidIssueReportCsvException $exception) {
            throw ValidationException::withMessages(['file' => $exception->getMessage()]);
        }

        return back()->with('toast', [
            'type' => 'success',
            'message' => "Issue backup restored: {$result['created']} created and {$result['updated']} updated.",
        ]);
    }

    public function update(
        UpdateIssueReportRequest $request,
        IssueReport $issueReport,
    ): RedirectResponse {
        $this->issueReportService->update($issueReport, $request->validated());

        return back()->with('toast', [
            'type' => 'success',
            'message' => 'Issue report updated successfully.',
        ]);
    }
}
