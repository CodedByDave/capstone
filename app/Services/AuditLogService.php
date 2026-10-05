<?php

namespace App\Services;

use App\Exceptions\InvalidAuditLogCsvException;
use App\Repositories\AuditLogRepository;
use Carbon\Carbon;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class AuditLogService
{
    private const CSV_COLUMNS = [
        'type', 'user', 'email', 'role', 'module', 'event',
        'shop', 'ip_address', 'user_agent', 'details', 'occurred_at',
    ];

    private const REQUIRED_CSV_COLUMNS = [
        'type', 'user', 'email', 'role', 'module', 'event', 'occurred_at',
    ];

    public function __construct(
        private readonly AuditLogRepository $auditLogRepository,
    ) {}

    public function getIndexData(array $filters): array
    {
        return [
            'logs' => $this->getPaginated($filters, (int) $filters['per_page']),
            'stats' => $this->auditLogRepository->getStats(),
            'modules' => $this->auditLogRepository->getModules(),
            'filters' => $filters,
        ];
    }

    public function getArchiveData(array $filters): array
    {
        return [
            'logs' => $this->getArchivedPaginated($filters, (int) $filters['per_page']),
            'total' => $this->auditLogRepository->getStats()['archived'],
            'filters' => $filters,
        ];
    }

    public function getPaginated(array $filters = [], int $perPage = 20): LengthAwarePaginator
    {
        return $this->auditLogRepository->getPaginated($filters, $perPage);
    }

    public function getArchivedPaginated(array $filters = [], int $perPage = 20): LengthAwarePaginator
    {
        return $this->auditLogRepository->getPaginated($filters, $perPage, archived: true);
    }

    public function writeCsvExport(array $filters): void
    {
        $output = fopen('php://output', 'w');

        if ($output === false) {
            throw new InvalidAuditLogCsvException('The audit log export could not be opened.');
        }

        try {
            fwrite($output, "\xEF\xBB\xBF");
            fputcsv($output, self::CSV_COLUMNS, ',', '"', '');

            foreach ($this->auditLogRepository->getForExport($filters) as $log) {
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
        } finally {
            fclose($output);
        }
    }

    public function importCsv(UploadedFile $file): array
    {
        $rows = $this->readCsvRows($file);

        return DB::transaction(function () use ($rows): array {
            return $this->persistImportedRows(collect($rows));
        });
    }

    public function find(string $category, int $id): object
    {
        return $this->auditLogRepository->find($category, $id);
    }

    public function archive(string $category, int $id): void
    {
        $this->auditLogRepository->archive($category, $id);
    }

    public function bulkArchive(array $entries): void
    {
        $this->auditLogRepository->bulkArchive($entries);
    }

    public function restore(string $category, int $id): void
    {
        $this->auditLogRepository->restore($category, $id);
    }

    public function bulkRestore(array $entries): void
    {
        $this->auditLogRepository->bulkRestore($entries);
    }

    public function forceDelete(string $category, int $id): void
    {
        $this->auditLogRepository->forceDelete($category, $id);
    }

    private function persistImportedRows(Collection $rows): array
    {
        $created = 0;
        $skipped = 0;
        $usersByEmail = $this->auditLogRepository->usersByEmail(
            $rows->pluck('email')->filter()->unique(),
        );
        $shopsByName = $this->auditLogRepository->shopsByName(
            $rows->pluck('shop')->filter()->unique(),
        );

        $authenticationRows = $rows->where('type', 'authentication');
        $existingAuthentication = $this->auditLogRepository->existingAuthenticationKeys(
            $authenticationRows->pluck('email')->filter()->unique(),
            $authenticationRows->pluck('occurred_at')->unique(),
        );
        $activityRows = $rows->where('type', 'activity');
        $existingActivities = $this->auditLogRepository->existingActivityKeys(
            $activityRows->pluck('occurred_at')->unique(),
        );

        foreach ($rows as $row) {
            $user = $row['email'] !== null ? $usersByEmail->get($row['email']) : null;

            if ($row['type'] === 'authentication') {
                $key = $row['email'].'|'.$row['event'].'|'.$row['occurred_at'];

                if ($existingAuthentication->has($key)) {
                    $skipped++;

                    continue;
                }

                $this->auditLogRepository->createAuthentication([
                    'user_id' => $user?->id,
                    'email' => $row['email'],
                    'name' => $row['user'],
                    'role' => $row['role'],
                    'ip_address' => $row['ip_address'],
                    'user_agent' => $row['user_agent'],
                    'status' => $row['event'],
                    'failure_reason' => $row['details'],
                    'logged_at' => $row['occurred_at'],
                ]);
                $existingAuthentication->put($key, true);
            } else {
                $module = $row['module'] ?? 'Imported Activity';
                $key = $module.'|'.$row['event'].'|'.($user?->id ?? 'null').'|'.$row['occurred_at'];

                if ($existingActivities->has($key)) {
                    $skipped++;

                    continue;
                }

                $changes = $this->decodeChanges($row['details']);
                $shop = $row['shop'] !== null ? $shopsByName->get($row['shop']) : null;
                $this->auditLogRepository->createActivity([
                    'module' => $module,
                    'action' => $row['event'],
                    'performed_by' => $user?->id,
                    'shop_id' => $shop?->id,
                    'changes' => $changes,
                ], $row['occurred_at']);
                $existingActivities->put($key, true);
            }

            $created++;
        }

        return compact('created', 'skipped');
    }

    private function readCsvRows(UploadedFile $file): array
    {
        $path = $file->getRealPath();
        if ($path === false) {
            throw new InvalidAuditLogCsvException('The audit backup file could not be opened.');
        }

        $handle = fopen($path, 'r');
        if ($handle === false) {
            throw new InvalidAuditLogCsvException('The audit backup file could not be opened.');
        }

        try {
            $header = fgetcsv($handle, 0, ',', '"', '');
            if ($header === false) {
                throw new InvalidAuditLogCsvException('The audit backup file is empty.');
            }

            $header = array_map(function (mixed $column): string {
                $column = preg_replace('/^\xEF\xBB\xBF/', '', (string) $column);

                return Str::snake(trim($column));
            }, $header);

            $missing = array_diff(self::REQUIRED_CSV_COLUMNS, $header);
            if ($missing !== []) {
                throw new InvalidAuditLogCsvException(
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
        $type = strtolower(trim((string) ($row['type'] ?? '')));
        $event = trim((string) ($row['event'] ?? ''));
        $occurredAt = trim((string) ($row['occurred_at'] ?? ''));
        $email = strtolower(trim((string) ($row['email'] ?? '')));

        if (! in_array($type, ['authentication', 'activity'], true)) {
            throw new InvalidAuditLogCsvException("Row {$rowNumber} has an invalid audit type.");
        }

        if ($type === 'authentication') {
            $event = strtolower($event);
            if (! in_array($event, ['success', 'failed', 'logout'], true)) {
                throw new InvalidAuditLogCsvException(
                    "Row {$rowNumber} has an invalid authentication event.",
                );
            }

            if (! filter_var($email, FILTER_VALIDATE_EMAIL)) {
                throw new InvalidAuditLogCsvException(
                    "Row {$rowNumber} has an invalid email address.",
                );
            }
        }

        if ($occurredAt === '' || strtotime($occurredAt) === false) {
            throw new InvalidAuditLogCsvException(
                "Row {$rowNumber} has an invalid occurred_at value.",
            );
        }

        return [
            'type' => $type,
            'user' => trim((string) ($row['user'] ?? '')) ?: null,
            'email' => $email ?: null,
            'role' => trim((string) ($row['role'] ?? '')) ?: null,
            'module' => trim((string) ($row['module'] ?? '')) ?: null,
            'event' => $event,
            'shop' => trim((string) ($row['shop'] ?? '')) ?: null,
            'ip_address' => trim((string) ($row['ip_address'] ?? '')) ?: null,
            'user_agent' => trim((string) ($row['user_agent'] ?? '')) ?: null,
            'details' => trim((string) ($row['details'] ?? '')) ?: null,
            'occurred_at' => Carbon::parse($occurredAt)->format('Y-m-d H:i:s'),
        ];
    }

    private function decodeChanges(?string $details): ?array
    {
        if ($details === null) {
            return null;
        }

        $decoded = json_decode($details, true);

        return json_last_error() === JSON_ERROR_NONE
            ? $decoded
            : ['imported_details' => $details];
    }
}
