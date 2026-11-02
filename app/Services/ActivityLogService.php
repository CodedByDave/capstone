<?php

namespace App\Services;

use App\DTOs\TableSort;
use App\Models\ActivityLog;
use App\Models\Shop;
use App\Models\User;
use App\Repositories\ActivityLogRepository;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class ActivityLogService
{
    public function __construct(
        private readonly ActivityLogRepository $activityLogs,
    ) {}

    public function log(
        Model $subject,
        string $action,
        ?array $changes = null,
        ?int $shopId = null,
        ?string $module = null,
    ): void {
        ActivityLog::create([
            'module' => $module ?? class_basename($subject),
            'action' => $action,
            'subject_type' => $subject->getMorphClass(),
            'subject_id' => $subject->getKey(),
            'performed_by' => auth()->id(),
            'shop_id' => $shopId,
            'changes' => $changes,
        ]);
    }

    /**
     * One page of a shop's activity logs, filtered by the request and ordered by the table sort.
     *
     * @param  array<int>|null  $performerIds
     */
    public function getPaginatedLogs(Shop $shop, object $request, ?array $performerIds = null, ?TableSort $sort = null): LengthAwarePaginator
    {
        return $this->activityLogs->paginateForShop(
            $shop->id,
            [
                'action' => $request->action ?? null,
                'performed_by' => $request->performed_by ?? null,
                'module' => $request->module ?? null,
                'date_from' => $request->date_from ?? null,
                'date_to' => $request->date_to ?? null,
                'search' => $request->search ?? null,
            ],
            $performerIds,
            (int) ($request->per_page ?? 10),
            $sort,
        );
    }

    /**
     * @param  array<int>|null  $performerIds
     * @return array{all: int, created: int, updated: int, archived: int}
     */
    public function getActionCounts(Shop $shop, ?array $performerIds = null): array
    {
        $counts = $this->activityLogs->countsByAction($shop->id, $performerIds);

        return [
            'all' => (int) $counts->sum(),
            'created' => (int) ($counts['created'] ?? 0),
            'updated' => (int) ($counts['updated'] ?? 0),
            'archived' => (int) ($counts['archived'] ?? 0),
        ];
    }

    /**
     * Recent activity performed by an employee's user account, shaped for display.
     *
     * @return array<int, array<string, mixed>>
     */
    public function getEmployeeActivity(Shop $shop, ?int $userId, int $limit = 10): array
    {
        if ($userId === null) {
            return [];
        }

        return $this->activityLogs->recentByPerformer($shop->id, $userId, $limit)
            ->map(function (ActivityLog $log) {
                $subject = $log->subject;
                $label = match (true) {
                    $subject === null => 'Deleted Record',
                    isset($subject->first_name) => trim($subject->first_name.' '.($subject->last_name ?? '')),
                    isset($subject->name) => $subject->name,
                    default => '#'.$log->subject_id,
                };

                return [
                    'id' => $log->id,
                    'module' => $log->module,
                    'action' => $log->action,
                    'subject' => $label,
                    'created_at' => $log->created_at,
                ];
            })
            ->all();
    }

    public function getPerformers(Shop $shop, ?array $performerIds = null): Collection
    {
        return User::whereIn(
            'id',
            ActivityLog::where('shop_id', $shop->id)
                ->when($performerIds !== null, fn ($q) => $q->whereIn('performed_by', $performerIds))
                ->whereNotNull('performed_by')
                ->distinct()
                ->pluck('performed_by')
        )->select('id', 'name')->get();
    }

    public function getModules(Shop $shop, ?array $performerIds = null): Collection
    {
        return ActivityLog::where('shop_id', $shop->id)
            ->when($performerIds !== null, fn ($q) => $q->whereIn('performed_by', $performerIds))
            ->distinct()
            ->pluck('module');
    }
}
