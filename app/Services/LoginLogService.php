<?php

namespace App\Services;

use App\Models\LoginLog;
use App\Repositories\LoginLogRepository;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class LoginLogService
{
    public function __construct(
        private readonly LoginLogRepository $loginLogRepository,
    ) {}

    // ── Write (called by listeners) ───────────────────────────────────────────

    public function logSuccess(
        int $userId,
        string $email,
        string $name,
        string $role,
        string $ip,
        string $userAgent,
    ): void {
        $this->loginLogRepository->create([
            'user_id'    => $userId,
            'email'      => $email,
            'name'       => $name,
            'role'       => $role,
            'ip_address' => $ip,
            'user_agent' => $userAgent,
            'status'     => 'success',
            'logged_at'  => now(),
        ]);
    }

    public function logFailed(
        ?int $userId,
        string $email,
        ?string $name,
        ?string $role,
        string $ip,
        string $userAgent,
    ): void {
        $this->loginLogRepository->create([
            'user_id'        => $userId,
            'email'          => $email,
            'name'           => $name,
            'role'           => $role,
            'ip_address'     => $ip,
            'user_agent'     => $userAgent,
            'status'         => 'failed',
            'failure_reason' => 'Invalid credentials',
            'logged_at'      => now(),
        ]);
    }

    public function logLogout(
        int $userId,
        string $email,
        string $name,
        string $role,
        string $ip,
        string $userAgent,
    ): void {
        $this->loginLogRepository->create([
            'user_id'    => $userId,
            'email'      => $email,
            'name'       => $name,
            'role'       => $role,
            'ip_address' => $ip,
            'user_agent' => $userAgent,
            'status'     => 'logout',
            'logged_at'  => now(),
        ]);
    }

    // ── Read ──────────────────────────────────────────────────────────────────

    public function getPaginated(array $filters = [], int $perPage = 20): LengthAwarePaginator
    {
        return $this->loginLogRepository->getPaginated($filters, $perPage);
    }

    public function getStats(): array
    {
        return $this->loginLogRepository->getStats();
    }

    public function getArchivedPaginated(array $filters = [], int $perPage = 20): LengthAwarePaginator
    {
        return $this->loginLogRepository->getArchivedPaginated($filters, $perPage);
    }

    public function getArchivedTotal(): int
    {
        return $this->loginLogRepository->getArchivedTotal();
    }

    // ── Archive ───────────────────────────────────────────────────────────────

    public function archive(LoginLog $log): void
    {
        $this->loginLogRepository->archiveSingle($log);
    }

    public function bulkArchive(array $ids): void
    {
        $this->loginLogRepository->bulkArchive($ids);
    }

    public function restore(int $id): void
    {
        $this->loginLogRepository->restore($id);
    }

    public function bulkRestore(array $ids): void
    {
        $this->loginLogRepository->bulkRestore($ids);
    }

    public function forceDelete(int $id): void
    {
        $this->loginLogRepository->forceDelete($id);
    }
}
