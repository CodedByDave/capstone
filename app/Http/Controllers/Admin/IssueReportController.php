<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\IssueReport;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;

class IssueReportController extends Controller
{
    private const CATEGORIES = ['bug', 'account', 'billing', 'feature_request', 'general'];

    private const PRIORITIES = ['low', 'medium', 'high', 'critical'];

    private const STATUSES = ['open', 'in_progress', 'resolved', 'closed'];

    public function index(Request $request)
    {
        $filters = $request->only([
            'search', 'status', 'priority', 'category', 'sort_by',
            'sort_direction', 'per_page',
        ]);
        $perPage = min(max($request->integer('per_page', 15), 5), 100);
        $filters['per_page'] = (string) $perPage;

        $query = IssueReport::query()->with('reporter:id,public_id,name,email,role');
        $this->applyFilters($query, $request);
        $this->applySorting($query, $request);

        return Inertia::render('admin/issues/Index', [
            'reports' => $query->paginate($perPage)->withQueryString(),
            'stats' => [
                'total' => IssueReport::count(),
                'open' => IssueReport::where('status', 'open')->count(),
                'in_progress' => IssueReport::where('status', 'in_progress')->count(),
                'resolved' => IssueReport::where('status', 'resolved')->count(),
                'critical' => IssueReport::where('priority', 'critical')
                    ->whereNot('status', 'resolved')
                    ->count(),
            ],
            'filters' => $filters,
        ]);
    }

    public function exportCsv(Request $request)
    {
        $query = IssueReport::query()->with('reporter:id,name,email');
        $this->applyFilters($query, $request);
        $this->applySorting($query, $request);
        $reports = $query->get();

        return response()->streamDownload(function () use ($reports) {
            $output = fopen('php://output', 'w');
            fwrite($output, "\xEF\xBB\xBF");
            fputcsv($output, [
                'public_id', 'reporter_email', 'reporter_name', 'subject',
                'description', 'category', 'priority', 'status', 'page_url',
                'browser', 'admin_notes', 'resolved_at', 'reported_at',
            ], ',', '"', '');

            foreach ($reports as $report) {
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

            fclose($output);
        }, 'issue-reports-'.now()->format('Y-m-d-His').'.csv', [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    public function importCsv(Request $request)
    {
        $validated = $request->validate([
            'file' => ['required', 'file', 'mimes:csv,txt', 'max:5120'],
        ]);
        $handle = fopen($validated['file']->getRealPath(), 'r');

        if ($handle === false) {
            throw ValidationException::withMessages([
                'file' => 'The issue report CSV could not be opened.',
            ]);
        }

        try {
            $header = fgetcsv($handle, 0, ',', '"', '');
            if ($header === false) {
                throw ValidationException::withMessages(['file' => 'The CSV file is empty.']);
            }

            $header = array_map(function ($column) {
                $column = preg_replace('/^\xEF\xBB\xBF/', '', (string) $column);

                return Str::snake(trim($column));
            }, $header);
            $required = ['public_id', 'subject', 'description', 'category', 'priority', 'status'];
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
                $publicId = trim((string) ($row['public_id'] ?? ''));
                $subject = trim((string) ($row['subject'] ?? ''));
                $description = trim((string) ($row['description'] ?? ''));
                $category = strtolower(trim((string) ($row['category'] ?? '')));
                $priority = strtolower(trim((string) ($row['priority'] ?? '')));
                $status = strtolower(trim((string) ($row['status'] ?? '')));
                $email = strtolower(trim((string) ($row['reporter_email'] ?? '')));
                $resolvedAt = trim((string) ($row['resolved_at'] ?? ''));
                $reportedAt = trim((string) ($row['reported_at'] ?? ''));

                if (! Str::isUlid($publicId) || $subject === '' || $description === '') {
                    throw ValidationException::withMessages([
                        'file' => "Row {$rowNumber} contains an invalid ID, subject, or description.",
                    ]);
                }
                if (! in_array($category, self::CATEGORIES, true)
                    || ! in_array($priority, self::PRIORITIES, true)
                    || ! in_array($status, self::STATUSES, true)) {
                    throw ValidationException::withMessages([
                        'file' => "Row {$rowNumber} contains an invalid category, priority, or status.",
                    ]);
                }
                if ($email !== '' && ! filter_var($email, FILTER_VALIDATE_EMAIL)) {
                    throw ValidationException::withMessages([
                        'file' => "Row {$rowNumber} contains an invalid reporter_email.",
                    ]);
                }
                if (($resolvedAt !== '' && strtotime($resolvedAt) === false)
                    || ($reportedAt !== '' && strtotime($reportedAt) === false)) {
                    throw ValidationException::withMessages([
                        'file' => "Row {$rowNumber} contains an invalid date.",
                    ]);
                }

                $rows[] = [
                    'public_id' => $publicId,
                    'reporter_email' => $email ?: null,
                    'subject' => $subject,
                    'description' => $description,
                    'category' => $category,
                    'priority' => $priority,
                    'status' => $status,
                    'page_url' => trim((string) ($row['page_url'] ?? '')) ?: null,
                    'browser' => trim((string) ($row['browser'] ?? '')) ?: null,
                    'admin_notes' => trim((string) ($row['admin_notes'] ?? '')) ?: null,
                    'resolved_at' => $resolvedAt ?: null,
                    'reported_at' => $reportedAt ?: null,
                ];
            }
        } finally {
            fclose($handle);
        }

        [$created, $updated] = DB::transaction(function () use ($rows) {
            $created = 0;
            $updated = 0;
            $users = User::whereIn('email', collect($rows)->pluck('reporter_email')->filter()->unique())
                ->get()
                ->keyBy(fn (User $user) => strtolower($user->email));

            foreach ($rows as $row) {
                $report = IssueReport::where('public_id', $row['public_id'])->first();
                $report === null ? $created++ : $updated++;
                $report ??= new IssueReport;
                $report->forceFill([
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
                ])->save();
            }

            return [$created, $updated];
        });

        return back()->with('toast', [
            'type' => 'success',
            'message' => "Issue backup restored: {$created} created and {$updated} updated.",
        ]);
    }

    public function update(Request $request, IssueReport $issueReport)
    {
        $validated = $request->validate([
            'status' => ['required', Rule::in(self::STATUSES)],
            'priority' => ['required', Rule::in(self::PRIORITIES)],
            'admin_notes' => ['nullable', 'string', 'max:5000'],
        ]);
        $validated['resolved_at'] = $validated['status'] === 'resolved'
            ? ($issueReport->resolved_at ?? now())
            : null;
        $issueReport->update($validated);

        return back()->with('toast', [
            'type' => 'success',
            'message' => 'Issue report updated successfully.',
        ]);
    }

    private function applyFilters(Builder $query, Request $request): void
    {
        $query->when($request->filled('search'), function (Builder $query) use ($request) {
            $search = $request->string('search')->toString();
            $query->where(function (Builder $query) use ($search) {
                $query->where('subject', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%")
                    ->orWhereHas('reporter', fn (Builder $reporter) => $reporter
                        ->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%"));
            });
        });
        $query->when($request->filled('status'), fn (Builder $query) => $query->where('status', $request->string('status')->toString()));
        $query->when($request->filled('priority'), fn (Builder $query) => $query->where('priority', $request->string('priority')->toString()));
        $query->when($request->filled('category'), fn (Builder $query) => $query->where('category', $request->string('category')->toString()));
    }

    private function applySorting(Builder $query, Request $request): void
    {
        $sortBy = $request->string('sort_by')->toString();
        $direction = $request->string('sort_direction')->toString() === 'asc' ? 'asc' : 'desc';
        $columns = [
            'subject' => 'subject',
            'category' => 'category',
            'priority' => 'priority',
            'status' => 'status',
            'created_at' => 'created_at',
        ];

        if ($sortBy === 'reporter') {
            $query->orderBy(
                User::query()
                    ->select('name')
                    ->whereColumn('users.id', 'issue_reports.user_id')
                    ->limit(1),
                $direction,
            )->orderBy('issue_reports.id', $direction);

            return;
        }

        $column = $columns[$sortBy] ?? 'created_at';
        $query->orderBy("issue_reports.{$column}", $direction)
            ->orderBy('issue_reports.id', $direction);
    }
}
