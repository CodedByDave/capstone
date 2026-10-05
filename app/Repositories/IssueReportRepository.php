<?php

namespace App\Repositories;

use App\Models\IssueReport;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class IssueReportRepository extends Repository
{
    public function __construct()
    {
        parent::__construct(new IssueReport);
    }

    public function getPaginated(array $filters, int $perPage): LengthAwarePaginator
    {
        return $this->filteredQuery($filters)
            ->with('reporter:id,public_id,name,email,role')
            ->paginate($perPage)
            ->withQueryString();
    }

    public function getForExport(array $filters): Collection
    {
        return $this->filteredQuery($filters)
            ->with('reporter:id,name,email')
            ->get();
    }

    public function getStats(): array
    {
        return [
            'total' => $this->query()->count(),
            'open' => $this->query()->where('status', 'open')->count(),
            'in_progress' => $this->query()->where('status', 'in_progress')->count(),
            'resolved' => $this->query()->where('status', 'resolved')->count(),
            'critical' => $this->query()
                ->where('priority', 'critical')
                ->whereNot('status', 'resolved')
                ->count(),
        ];
    }

    public function usersByEmail(Collection $emails): Collection
    {
        return User::whereIn('email', $emails)
            ->get()
            ->keyBy(fn (User $user) => strtolower($user->email));
    }

    public function findByPublicId(string $publicId): ?IssueReport
    {
        return IssueReport::where('public_id', $publicId)->first();
    }

    public function saveImported(?IssueReport $report, array $attributes): IssueReport
    {
        $report ??= new IssueReport;
        $report->forceFill($attributes)->save();

        return $report;
    }

    public function updateReport(IssueReport $report, array $attributes): void
    {
        $report->update($attributes);
    }

    private function filteredQuery(array $filters): Builder
    {
        $query = $this->query();

        if (! empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function (Builder $query) use ($search) {
                $query->where('subject', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%")
                    ->orWhereHas('reporter', fn (Builder $reporter) => $reporter
                        ->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%"));
            });
        }

        foreach (['status', 'priority', 'category'] as $filter) {
            if (! empty($filters[$filter])) {
                $query->where($filter, $filters[$filter]);
            }
        }

        return $this->applySorting($query, $filters);
    }

    private function applySorting(Builder $query, array $filters): Builder
    {
        $sortBy = $filters['sort_by'] ?? 'created_at';
        $direction = ($filters['sort_direction'] ?? 'desc') === 'asc' ? 'asc' : 'desc';

        if ($sortBy === 'reporter') {
            return $query->orderBy(
                User::query()
                    ->select('name')
                    ->whereColumn('users.id', 'issue_reports.user_id')
                    ->limit(1),
                $direction,
            )->orderBy('issue_reports.id', $direction);
        }

        return $query
            ->orderBy("issue_reports.{$sortBy}", $direction)
            ->orderBy('issue_reports.id', $direction);
    }
}
