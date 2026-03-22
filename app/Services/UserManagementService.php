<?php

namespace App\Services;

use App\Models\User;
use App\Repositories\UserRepository;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class UserManagementService
{
    public function __construct(
        private readonly UserRepository $userRepository,
    ) {}

    public function getPaginated(array $filters = [], int $perPage = 20): LengthAwarePaginator
    {
        return $this->userRepository->getPaginated($filters, $perPage);
    }

    public function getStats(): array
    {
        return $this->userRepository->getStats();
    }

    public function find(int $id): User
    {
        return $this->userRepository->findWithRelations($id);
    }

    public function archive(User $user): void
    {
        abort_if($user->role === 'super_admin', 403, 'Cannot archive super admin.');
        $this->userRepository->archiveUser($user);
    }

    public function bulkArchive(array $ids): void
    {
        $this->userRepository->bulkArchive($ids);
    }

    public function getArchivedPaginated(array $filters = [], int $perPage = 20): LengthAwarePaginator
    {
        return $this->userRepository->getArchivedPaginated($filters, $perPage);
    }

    public function getArchivedTotal(): int
    {
        return $this->userRepository->getArchivedTotal();
    }

    public function restore(int $id): void
    {
        $this->userRepository->restore($id);
    }

    public function bulkRestore(array $ids): void
    {
        $this->userRepository->bulkRestore($ids);
    }

    public function forceDelete(int $id): void
    {
        $this->userRepository->forceDelete($id);
    }
}
