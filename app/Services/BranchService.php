<?php

namespace App\Services;

use App\Models\Branch;
use App\Repositories\BranchRepository;
use App\Repositories\EmployeeRepository;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class BranchService
{
    private const TRACKED_FIELDS = [
        'name', 'phone', 'email',
        'manager_name', 'address',
        'status', 'opened_at',
        'mayors_permit_number', 'mayors_expiry_date',
        'sanitary_permit_number', 'sanitary_expiry_date',
    ];

    public function __construct(
        protected BranchRepository $repository,
        private readonly EmployeeRepository $employeeRepository,
        private readonly ActivityLogService $activityLogService,
    ) {}

    /* ─── READ ─────────────────────────────────── */

    public function indexData(int $shopId, array $filters = [], ?string $branch = null): array
    {
        $filters = [
            'search' => $filters['search'] ?? '',
            'status' => $filters['status'] ?? '',
            'sort_by' => $filters['sort_by'] ?? 'branch_code',
            'sort_direction' => $filters['sort_direction'] ?? 'asc',
            'per_page' => (int) ($filters['per_page'] ?? 15),
        ];

        return [
            'branches' => $this->repository->paginateForShop($shopId, $filters, $branch),
            'stats' => $this->repository->statsForShop($shopId, $branch),
            'filters' => $filters,
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

    public function managerOptionsForShop(int $shopId): array
    {
        return $this->employeeRepository->activeManagerOptionsForShop($shopId);
    }

    /* ─── WRITE ─────────────────────────────────── */

    public function create(array $validated, int $shopId): Branch
    {
        return DB::transaction(function () use ($validated, $shopId): Branch {
            $branch = $this->repository->create([
                ...$this->withoutPermitFiles($validated),
                'permit_status' => 'submitted',
                'shop_id' => $shopId,
                'created_by' => Auth::id(),
                'updated_by' => Auth::id(),
            ]);

            $permitPaths = $this->storePermitFiles($branch, $validated);
            if ($permitPaths !== []) {
                $this->repository->update($branch, $permitPaths);
            }

            $this->activityLogService->log(
                subject: $branch,
                action: 'created',
                shopId: $shopId,
            );

            return $branch->refresh();
        });
    }

    public function update(Branch $branch, array $validated): bool
    {
        return DB::transaction(function () use ($branch, $validated): bool {
            $changes = $this->diffChanges($branch, $validated);
            $permitPaths = $this->storePermitFiles($branch, $validated);
            $attributes = [
                ...$this->withoutPermitFiles($validated),
                ...$permitPaths,
                'updated_by' => Auth::id(),
            ];

            if ($permitPaths !== []) {
                $attributes['permit_status'] = 'submitted';
            }

            $result = $this->repository->update($branch, $attributes);

            if ($result && (! empty($changes) || $permitPaths !== [])) {
                $this->activityLogService->log(
                    subject: $branch->fresh(),
                    action: 'updated',
                    changes: $changes,
                    shopId: $branch->shop_id,
                );
            }

            return $result;
        });
    }

    public function archive(Branch $branch): bool
    {
        // Log BEFORE delete so subject still exists
        $this->activityLogService->log(
            subject: $branch,
            action: 'archived',
            shopId: $branch->shop_id,
        );

        return $this->repository->delete($branch);
    }

    public function restore(int $id, int $shopId): void
    {
        $branch = $this->repository->findTrashedOrFail($id);
        abort_if($branch->shop_id !== $shopId, 403, 'You do not have permission to restore this branch.');
        $this->repository->restore($branch);

        // Log AFTER restore so subject is active again
        $this->activityLogService->log(
            subject: $branch,
            action: 'restored',
            shopId: $branch->shop_id,
        );
    }

    /* ─── PRIVATE ────────────────────────────────── */

    private function diffChanges(Branch $branch, array $data): array
    {
        $changes = [];

        foreach (self::TRACKED_FIELDS as $field) {
            if (! array_key_exists($field, $data)) {
                continue;
            }

            $old = (string) $branch->getAttribute($field);
            $new = (string) $data[$field];

            if ($old === $new) {
                continue;
            }
            if (empty($old) && empty($new)) {
                continue;
            }

            $changes[$field] = ['old' => $old, 'new' => $new];
        }

        return $changes;
    }

    private function withoutPermitFiles(array $data): array
    {
        unset($data['mayors_permit'], $data['sanitary_permit']);

        return $data;
    }

    private function storePermitFiles(Branch $branch, array $data): array
    {
        $paths = [];

        foreach (['mayors_permit', 'sanitary_permit'] as $permit) {
            $file = $data[$permit] ?? null;
            if (! $file instanceof UploadedFile) {
                continue;
            }

            $path = $file->store("branch-permits/{$branch->id}", 'private');
            if (! $path) {
                throw new RuntimeException('A branch permit could not be stored. Please try again.');
            }

            $paths["{$permit}_path"] = $path;
        }

        return $paths;
    }
}
