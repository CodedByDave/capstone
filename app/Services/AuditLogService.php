<?php

namespace App\Services;

use App\Models\ActivityLog;
use App\Models\LoginLog;
use App\Models\Shop;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Query\Builder;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class AuditLogService
{
    public function getPaginated(array $filters = [], int $perPage = 20): LengthAwarePaginator
    {
        return $this->filteredQuery($filters)
            ->paginate($perPage)
            ->withQueryString();
    }

    public function getForCsvExport(array $filters = []): Collection
    {
        return $this->filteredQuery($filters)->get();
    }

    public function importCsv(UploadedFile $file): array
    {
        $handle = fopen($file->getRealPath(), 'r');

        if ($handle === false) {
            throw ValidationException::withMessages([
                'file' => 'The audit backup file could not be opened.',
            ]);
        }

        try {
            $header = fgetcsv($handle, 0, ',', '"', '');
            if ($header === false) {
                throw ValidationException::withMessages([
                    'file' => 'The audit backup file is empty.',
                ]);
            }

            $header = array_map(function ($column) {
                $column = preg_replace('/^\xEF\xBB\xBF/', '', (string) $column);

                return Str::snake(trim($column));
            }, $header);

            $required = ['type', 'user', 'email', 'role', 'module', 'event', 'occurred_at'];
            $missing = array_diff($required, $header);
            if ($missing !== []) {
                throw ValidationException::withMessages([
                    'file' => 'Missing required columns: '.implode(', ', $missing).'.',
                ]);
            }

            $rows = [];
            $rowNumber = 1;
            while (($values = fgetcsv($handle, 0, ',', '"', '')) !== false) {
                $rowNumber++;
                if (count(array_filter($values, fn ($value) => trim((string) $value) !== '')) === 0) {
                    continue;
                }

                $values = array_pad($values, count($header), null);
                $row = array_combine($header, array_slice($values, 0, count($header)));
                $type = strtolower(trim((string) ($row['type'] ?? '')));
                $event = strtolower(trim((string) ($row['event'] ?? '')));

                if (! in_array($type, ['authentication', 'activity'], true)) {
                    throw ValidationException::withMessages([
                        'file' => "Row {$rowNumber} has an invalid audit type.",
                    ]);
                }

                if ($type === 'authentication' && ! in_array($event, ['success', 'failed', 'logout'], true)) {
                    throw ValidationException::withMessages([
                        'file' => "Row {$rowNumber} has an invalid authentication event.",
                    ]);
                }

                if (trim((string) ($row['occurred_at'] ?? '')) === '' || strtotime($row['occurred_at']) === false) {
                    throw ValidationException::withMessages([
                        'file' => "Row {$rowNumber} has an invalid occurred_at value.",
                    ]);
                }

                if ($type === 'authentication' && ! filter_var($row['email'] ?? null, FILTER_VALIDATE_EMAIL)) {
                    throw ValidationException::withMessages([
                        'file' => "Row {$rowNumber} has an invalid email address.",
                    ]);
                }

                $rows[] = $row;
            }
        } finally {
            fclose($handle);
        }

        return DB::transaction(function () use ($rows) {
            $created = 0;
            $skipped = 0;
            $rowCollection = collect($rows);
            $usersByEmail = User::withTrashed()
                ->whereIn('email', $rowCollection->pluck('email')->map(fn ($email) => strtolower(trim((string) $email)))->filter()->unique())
                ->get()
                ->keyBy(fn (User $user) => strtolower($user->email));
            $shopsByName = Shop::withTrashed()
                ->whereIn('shop_name', $rowCollection->pluck('shop')->map(fn ($name) => trim((string) $name))->filter()->unique())
                ->get()
                ->keyBy('shop_name');

            $authenticationRows = $rowCollection->filter(
                fn ($row) => strtolower(trim((string) $row['type'])) === 'authentication',
            );
            $existingAuthentication = LoginLog::withTrashed()
                ->whereIn('email', $authenticationRows->pluck('email')->map(fn ($email) => strtolower(trim((string) $email)))->filter()->unique())
                ->whereIn('logged_at', $authenticationRows->pluck('occurred_at')->map(fn ($date) => Carbon::parse($date)->format('Y-m-d H:i:s'))->unique())
                ->get(['email', 'status', 'logged_at'])
                ->mapWithKeys(fn (LoginLog $log) => [
                    strtolower($log->email).'|'.$log->status.'|'.$log->logged_at->format('Y-m-d H:i:s') => true,
                ]);

            $activityRows = $rowCollection->filter(
                fn ($row) => strtolower(trim((string) $row['type'])) === 'activity',
            );
            $existingActivities = ActivityLog::withTrashed()
                ->whereIn('created_at', $activityRows->pluck('occurred_at')->map(fn ($date) => Carbon::parse($date)->format('Y-m-d H:i:s'))->unique())
                ->get(['module', 'action', 'performed_by', 'created_at'])
                ->mapWithKeys(fn (ActivityLog $log) => [
                    $log->module.'|'.$log->action.'|'.($log->performed_by ?? 'null').'|'.$log->created_at->format('Y-m-d H:i:s') => true,
                ]);

            foreach ($rows as $row) {
                $type = strtolower(trim((string) $row['type']));
                $email = strtolower(trim((string) ($row['email'] ?? '')));
                $occurredAt = Carbon::parse($row['occurred_at'])->format('Y-m-d H:i:s');
                $user = $email !== '' ? $usersByEmail->get($email) : null;

                if ($type === 'authentication') {
                    $event = strtolower(trim((string) $row['event']));
                    $authenticationKey = $email.'|'.$event.'|'.$occurredAt;

                    if ($existingAuthentication->has($authenticationKey)) {
                        $skipped++;

                        continue;
                    }

                    LoginLog::create([
                        'user_id' => $user?->id,
                        'email' => $email,
                        'name' => trim((string) ($row['user'] ?? '')) ?: null,
                        'role' => trim((string) ($row['role'] ?? '')) ?: null,
                        'ip_address' => trim((string) ($row['ip_address'] ?? '')) ?: null,
                        'user_agent' => trim((string) ($row['user_agent'] ?? '')) ?: null,
                        'status' => strtolower(trim((string) $row['event'])),
                        'failure_reason' => trim((string) ($row['details'] ?? '')) ?: null,
                        'logged_at' => $occurredAt,
                    ]);
                    $existingAuthentication->put($authenticationKey, true);
                } else {
                    $module = trim((string) ($row['module'] ?? '')) ?: 'Imported Activity';
                    $event = trim((string) ($row['event'] ?? ''));
                    $activityKey = $module.'|'.$event.'|'.($user?->id ?? 'null').'|'.$occurredAt;

                    if ($existingActivities->has($activityKey)) {
                        $skipped++;

                        continue;
                    }

                    $shopName = trim((string) ($row['shop'] ?? ''));
                    $shop = $shopName !== '' ? $shopsByName->get($shopName) : null;
                    $details = trim((string) ($row['details'] ?? ''));
                    $changes = null;
                    if ($details !== '') {
                        $decoded = json_decode($details, true);
                        $changes = json_last_error() === JSON_ERROR_NONE
                            ? $decoded
                            : ['imported_details' => $details];
                    }

                    $activity = new ActivityLog([
                        'module' => $module,
                        'action' => $event,
                        'performed_by' => $user?->id,
                        'shop_id' => $shop?->id,
                        'changes' => $changes,
                    ]);
                    $activity->created_at = $occurredAt;
                    $activity->updated_at = $occurredAt;
                    $activity->save();
                    $existingActivities->put($activityKey, true);
                }

                $created++;
            }

            return compact('created', 'skipped');
        });
    }

    public function find(string $category, int $id): object
    {
        $this->modelFor($category)->findOrFail($id);

        return DB::query()
            ->fromSub($this->unionQuery(), 'audit_entries')
            ->where('category', $category)
            ->where('record_id', $id)
            ->firstOrFail();
    }

    public function getArchivedPaginated(array $filters = [], int $perPage = 20): LengthAwarePaginator
    {
        return $this->filteredQuery($filters, archived: true)
            ->paginate($perPage)
            ->withQueryString();
    }

    public function getStats(): array
    {
        $authentication = LoginLog::query()->count();
        $activities = ActivityLog::query()->count();

        return [
            'total' => $authentication + $activities,
            'authentication' => $authentication,
            'activities' => $activities,
            'failed' => LoginLog::query()->where('status', 'failed')->count(),
            'archived' => LoginLog::onlyTrashed()->count() + ActivityLog::onlyTrashed()->count(),
        ];
    }

    public function getModules(): Collection
    {
        return ActivityLog::query()
            ->distinct()
            ->orderBy('module')
            ->pluck('module');
    }

    public function archive(string $category, int $id): void
    {
        $this->modelFor($category)->findOrFail($id)->delete();
    }

    public function bulkArchive(array $entries): void
    {
        collect($entries)
            ->groupBy('category')
            ->each(function (Collection $items, string $category) {
                $this->modelFor($category)
                    ->whereIn('id', $items->pluck('id'))
                    ->delete();
            });
    }

    public function restore(string $category, int $id): void
    {
        $this->modelFor($category)->onlyTrashed()->findOrFail($id)->restore();
    }

    public function bulkRestore(array $entries): void
    {
        collect($entries)
            ->groupBy('category')
            ->each(function (Collection $items, string $category) {
                $this->modelFor($category)
                    ->onlyTrashed()
                    ->whereIn('id', $items->pluck('id'))
                    ->restore();
            });
    }

    public function forceDelete(string $category, int $id): void
    {
        $this->modelFor($category)->onlyTrashed()->findOrFail($id)->forceDelete();
    }

    private function filteredQuery(array $filters, bool $archived = false): Builder
    {
        $query = DB::query()->fromSub($this->unionQuery($archived), 'audit_entries');

        if (! empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function (Builder $builder) use ($search) {
                $builder->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('ip_address', 'like', "%{$search}%")
                    ->orWhere('module', 'like', "%{$search}%")
                    ->orWhere('event', 'like', "%{$search}%")
                    ->orWhere('shop_name', 'like', "%{$search}%");
            });
        }

        if (! empty($filters['category'])) {
            $query->where('category', $filters['category']);
        }

        if (! empty($filters['role'])) {
            $query->where('role', $filters['role']);
        }

        if (! empty($filters['module'])) {
            $query->where('module', $filters['module']);
        }

        if (! empty($filters['event'])) {
            $query->where('event', $filters['event']);
        }

        if (! empty($filters['date'])) {
            $query->whereDate('occurred_at', $filters['date']);
        }

        $sortBy = $filters['sort_by'] ?? 'occurred_at';
        $sortDirection = ($filters['sort_direction'] ?? 'desc') === 'asc' ? 'asc' : 'desc';
        $sortable = ['name', 'email', 'category', 'module', 'event', 'occurred_at'];

        return $query->orderBy(
            in_array($sortBy, $sortable, true) ? $sortBy : 'occurred_at',
            $sortDirection,
        );
    }

    private function unionQuery(bool $archived = false): Builder
    {
        $authentication = DB::table('login_logs')
            ->leftJoin('users', 'users.id', '=', 'login_logs.user_id')
            ->when(
                $archived,
                fn (Builder $query) => $query->whereNotNull('login_logs.deleted_at'),
                fn (Builder $query) => $query->whereNull('login_logs.deleted_at'),
            )
            ->select([
                'login_logs.id as record_id',
                DB::raw("'authentication' as category"),
                'login_logs.user_id',
                'users.public_id as user_public_id',
                DB::raw('COALESCE(login_logs.name, users.name) as name'),
                DB::raw('COALESCE(login_logs.email, users.email) as email'),
                DB::raw('COALESCE(login_logs.role, users.role) as role'),
                DB::raw("'Authentication' as module"),
                'login_logs.status as event',
                'login_logs.failure_reason as details',
                'login_logs.ip_address',
                'login_logs.user_agent',
                DB::raw('NULL as shop_name'),
                'login_logs.logged_at as occurred_at',
                'login_logs.deleted_at as archived_at',
                DB::raw('1 as archivable'),
            ]);

        $activities = DB::table('activity_logs')
            ->leftJoin('users', 'users.id', '=', 'activity_logs.performed_by')
            ->leftJoin('shops', 'shops.id', '=', 'activity_logs.shop_id')
            ->when(
                $archived,
                fn (Builder $query) => $query->whereNotNull('activity_logs.deleted_at'),
                fn (Builder $query) => $query->whereNull('activity_logs.deleted_at'),
            )
            ->select([
                'activity_logs.id as record_id',
                DB::raw("'activity' as category"),
                'activity_logs.performed_by as user_id',
                'users.public_id as user_public_id',
                'users.name',
                'users.email',
                'users.role',
                'activity_logs.module',
                'activity_logs.action as event',
                'activity_logs.changes as details',
                DB::raw('NULL as ip_address'),
                DB::raw('NULL as user_agent'),
                'shops.shop_name',
                'activity_logs.created_at as occurred_at',
                'activity_logs.deleted_at as archived_at',
                DB::raw('1 as archivable'),
            ]);

        return $authentication->unionAll($activities);
    }

    private function modelFor(string $category): LoginLog|ActivityLog
    {
        return match ($category) {
            'authentication' => new LoginLog,
            'activity' => new ActivityLog,
            default => abort(404),
        };
    }
}
