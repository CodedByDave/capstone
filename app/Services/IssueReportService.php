<?php

namespace App\Services;

use App\Exceptions\InvalidIssueReportCsvException;
use App\Models\IssueReport;
use App\Repositories\IssueReportRepository;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class IssueReportService
{
    private const CSV_COLUMNS = [
        'public_id', 'reporter_email', 'reporter_name', 'subject',
        'description', 'category', 'priority', 'status', 'page_url',
        'browser', 'admin_notes', 'resolved_at', 'reported_at',
    ];

    private const REQUIRED_CSV_COLUMNS = [
        'public_id', 'subject', 'description', 'category', 'priority', 'status',
    ];

    public function __construct(
        private readonly IssueReportRepository $issueReportRepository,
    ) {}

    public function getIndexData(array $filters): array
    {
        return [
            'reports' => $this->issueReportRepository->getPaginated(
                $filters,
                (int) $filters['per_page'],
            ),
            'stats' => $this->issueReportRepository->getStats(),
            'filters' => $filters,
        ];
    }

    public function writeCsvExport(array $filters): void
    {
        $output = fopen('php://output', 'w');

        if ($output === false) {
            throw new InvalidIssueReportCsvException('The issue report export could not be opened.');
        }

        try {
            fwrite($output, "\xEF\xBB\xBF");
            fputcsv($output, self::CSV_COLUMNS, ',', '"', '');

            foreach ($this->issueReportRepository->getForExport($filters) as $report) {
                fputcsv($output, [
                    $report->public_id,
                    $report->reporter?->email,
                    $report->reporter?->name,
                    $report->subject,
                    $report->description,
                    $report->category,
                    $report->priority,
                    $report->status,
                    $report->page_url,
                    $report->browser,
                    $report->admin_notes,
                    $report->resolved_at?->toDateTimeString(),
                    $report->created_at?->toDateTimeString(),
                ], ',', '"', '');
            }
        } finally {
            fclose($output);
        }
    }

    public function importCsv(UploadedFile $file): array
    {
        $rows = $this->readCsvRows($file);

        return DB::transaction(function () use ($rows): array {
            $created = 0;
            $updated = 0;
            $emails = collect($rows)->pluck('reporter_email')->filter()->unique();
            $users = $this->issueReportRepository->usersByEmail($emails);

            foreach ($rows as $row) {
                $report = $this->issueReportRepository->findByPublicId($row['public_id']);
                $report === null ? $created++ : $updated++;

                $this->issueReportRepository->saveImported($report, [
                    'public_id' => $row['public_id'],
                    'user_id' => $row['reporter_email']
                        ? $users->get($row['reporter_email'])?->id
                        : null,
                    'subject' => $row['subject'],
                    'description' => $row['description'],
                    'category' => $row['category'],
                    'priority' => $row['priority'],
                    'status' => $row['status'],
                    'page_url' => $row['page_url'],
                    'browser' => $row['browser'],
                    'admin_notes' => $row['admin_notes'],
                    'resolved_at' => $row['resolved_at'],
                    'created_at' => $row['reported_at'] ?? now(),
                ]);
            }

            return compact('created', 'updated');
        });
    }

    public function update(IssueReport $issueReport, array $attributes): void
    {
        $attributes['resolved_at'] = $attributes['status'] === 'resolved'
            ? ($issueReport->resolved_at ?? now())
            : null;

        $this->issueReportRepository->updateReport($issueReport, $attributes);
    }

    private function readCsvRows(UploadedFile $file): array
    {
        $path = $file->getRealPath();

        if ($path === false) {
            throw new InvalidIssueReportCsvException('The issue report CSV could not be opened.');
        }

        $handle = fopen($path, 'r');

        if ($handle === false) {
            throw new InvalidIssueReportCsvException('The issue report CSV could not be opened.');
        }

        try {
            $header = fgetcsv($handle, 0, ',', '"', '');

            if ($header === false) {
                throw new InvalidIssueReportCsvException('The CSV file is empty.');
            }

            $header = array_map(function (mixed $column): string {
                $column = preg_replace('/^\xEF\xBB\xBF/', '', (string) $column);

                return Str::snake(trim($column));
            }, $header);

            $missing = array_diff(self::REQUIRED_CSV_COLUMNS, $header);
            if ($missing !== []) {
                throw new InvalidIssueReportCsvException(
                    'Missing required columns: '.implode(', ', $missing).'.',
                );
            }

            return $this->readDataRows($handle, $header);
        } finally {
            fclose($handle);
        }
    }

    private function readDataRows(mixed $handle, array $header): array
    {
        $rows = [];
        $rowNumber = 1;

        while (($values = fgetcsv($handle, 0, ',', '"', '')) !== false) {
            $rowNumber++;
            if (count(array_filter($values, fn (mixed $value) => trim((string) $value) !== '')) === 0) {
                continue;
            }

            $values = array_pad($values, count($header), null);
            $row = array_combine($header, array_slice($values, 0, count($header)));
            $rows[] = $this->normalizeAndValidateRow($row, $rowNumber);
        }

        return $rows;
    }

    private function normalizeAndValidateRow(array $row, int $rowNumber): array
    {
        $normalized = [
            'public_id' => trim((string) ($row['public_id'] ?? '')),
            'reporter_email' => strtolower(trim((string) ($row['reporter_email'] ?? ''))) ?: null,
            'subject' => trim((string) ($row['subject'] ?? '')),
            'description' => trim((string) ($row['description'] ?? '')),
            'category' => strtolower(trim((string) ($row['category'] ?? ''))),
            'priority' => strtolower(trim((string) ($row['priority'] ?? ''))),
            'status' => strtolower(trim((string) ($row['status'] ?? ''))),
            'page_url' => trim((string) ($row['page_url'] ?? '')) ?: null,
            'browser' => trim((string) ($row['browser'] ?? '')) ?: null,
            'admin_notes' => trim((string) ($row['admin_notes'] ?? '')) ?: null,
            'resolved_at' => trim((string) ($row['resolved_at'] ?? '')) ?: null,
            'reported_at' => trim((string) ($row['reported_at'] ?? '')) ?: null,
        ];

        if (! Str::isUlid($normalized['public_id'])
            || $normalized['subject'] === ''
            || $normalized['description'] === '') {
            throw new InvalidIssueReportCsvException(
                "Row {$rowNumber} contains an invalid ID, subject, or description.",
            );
        }

        if (! in_array($normalized['category'], IssueReport::CATEGORIES, true)
            || ! in_array($normalized['priority'], IssueReport::PRIORITIES, true)
            || ! in_array($normalized['status'], IssueReport::STATUSES, true)) {
            throw new InvalidIssueReportCsvException(
                "Row {$rowNumber} contains an invalid category, priority, or status.",
            );
        }

        if ($normalized['reporter_email'] !== null
            && ! filter_var($normalized['reporter_email'], FILTER_VALIDATE_EMAIL)) {
            throw new InvalidIssueReportCsvException(
                "Row {$rowNumber} contains an invalid reporter_email.",
            );
        }

        if (($normalized['resolved_at'] !== null && strtotime($normalized['resolved_at']) === false)
            || ($normalized['reported_at'] !== null && strtotime($normalized['reported_at']) === false)) {
            throw new InvalidIssueReportCsvException("Row {$rowNumber} contains an invalid date.");
        }

        return $normalized;
    }
}
