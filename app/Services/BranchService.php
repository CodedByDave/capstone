<?php

namespace App\Services;

use App\Models\Branch;
use App\Models\Shop;
use App\Services\ActivityLogService;
use App\Repositories\BranchRepository;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;

class BranchService
{
    private const TRACKED_FIELDS = [
        'name', 'phone', 'email',
        'manager_name', 'address',
        'status', 'opened_at',
    ];

    public function __construct(
        protected BranchRepository $repository,
        private readonly ActivityLogService $activityLogService,
    ) {}

    /* ─── READ ─────────────────────────────────── */

    public function indexData(int $shopId): array
    {
        return [
            'branches' => $this->repository->paginateForShop($shopId),
            'stats'    => $this->repository->statsForShop($shopId),
        ];
    }

    public function archiveData(int $shopId): LengthAwarePaginator
    {
        return $this->repository->paginateArchivedForShop($shopId);
    }

    public function activeBranchNames(int $shopId): Collection
    {
        return $this->repository->activeBranchNamesForShop($shopId);
    }

    /* ─── WRITE ─────────────────────────────────── */

    public function create(array $validated, int $shopId): Branch
    {
        $branch = $this->repository->create([
            ...$validated,
            'shop_id'    => $shopId,
            'created_by' => Auth::id(),
            'updated_by' => Auth::id(),
        ]);

        // Log creation
        $this->activityLogService->log(
            subject: $branch,
            action:  'created',
            shopId:  $shopId,
        );

        return $branch;
    }

    public function update(Branch $branch, array $validated): bool
    {
        $this->authorizeShop($branch);

        $changes = $this->diffChanges($branch, $validated);

        $result = $this->repository->update($branch, [
            ...$validated,
            'updated_by' => Auth::id(),
        ]);

        // Only log if something actually changed
        if ($result && !empty($changes)) {
            $this->activityLogService->log(
                subject: $branch->fresh(), // fresh() gets updated values
                action:  'updated',
                changes: $changes,
                shopId:  $branch->shop_id,
            );
        }

        return $result;
    }

    public function archive(Branch $branch): bool
    {
        $this->authorizeShop($branch);

        // Log BEFORE delete so subject still exists
        $this->activityLogService->log(
            subject: $branch,
            action:  'archived',
            shopId:  $branch->shop_id,
        );

        return $this->repository->delete($branch);
    }

    public function restore(int $id): void
    {
        $branch = $this->repository->findTrashedOrFail($id);
        $this->authorizeShop($branch);
        $this->repository->restore($branch);

        // Log AFTER restore so subject is active again
        $this->activityLogService->log(
            subject: $branch,
            action:  'restored',
            shopId:  $branch->shop_id,
        );
    }

    /* ─── PRIVATE ────────────────────────────────── */

    private function diffChanges(Branch $branch, array $data): array
    {
        $changes = [];

        foreach (self::TRACKED_FIELDS as $field) {
            if (!array_key_exists($field, $data)) continue;

            $old = (string) $branch->getAttribute($field);
            $new = (string) $data[$field];

            if ($old === $new) continue;
            if (empty($old) && empty($new)) continue;

            $changes[$field] = ['old' => $old, 'new' => $new];
        }

        return $changes;
    }

    private function authorizeShop(Branch $branch): void
    {
        abort_if(
            $branch->shop_id !== Auth::user()->shop->id,
            403,
            'You do not have permission to modify this branch.'
        );
    }
}
