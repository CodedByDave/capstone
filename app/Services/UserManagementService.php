<?php

namespace App\Services;

use App\Enums\AccountType;
use App\Models\User;
use App\Repositories\UserRepository;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

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

    public function getCsvExportUsers(): Collection
    {
        return $this->userRepository->getForCsvExport();
    }

    public function importCsv(UploadedFile $file): array
    {
        $handle = fopen($file->getRealPath(), 'r');

        if ($handle === false) {
            throw ValidationException::withMessages([
                'file' => 'The CSV file could not be opened.',
            ]);
        }

        try {
            $header = fgetcsv($handle, 0, ',', '"', '');

            if ($header === false) {
                throw ValidationException::withMessages([
                    'file' => 'The CSV file is empty.',
                ]);
            }

            $header = array_map(function ($column) {
                $column = preg_replace('/^\xEF\xBB\xBF/', '', (string) $column);

                return Str::snake(trim($column));
            }, $header);

            $requiredColumns = ['name', 'email', 'role', 'verified'];
            $missingColumns = array_diff($requiredColumns, $header);

            if ($missingColumns !== []) {
                throw ValidationException::withMessages([
                    'file' => 'Missing required columns: '.implode(', ', $missingColumns).'.',
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
                $role = strtolower(trim((string) ($row['role'] ?? '')));
                $verified = strtolower(trim((string) ($row['verified'] ?? '')));

                if (! filter_var($row['email'] ?? null, FILTER_VALIDATE_EMAIL)) {
                    throw ValidationException::withMessages([
                        'file' => "Row {$rowNumber} contains an invalid email address.",
                    ]);
                }

                if (! in_array($role, [
                    AccountType::ShopOwner->value,
                    AccountType::Staff->value,
                    AccountType::Customer->value,
                ], true)) {
                    throw ValidationException::withMessages([
                        'file' => "Row {$rowNumber} contains an invalid role.",
                    ]);
                }

                if (trim((string) ($row['name'] ?? '')) === '') {
                    throw ValidationException::withMessages([
                        'file' => "Row {$rowNumber} is missing the user's name.",
                    ]);
                }

                $joinedAt = trim((string) ($row['joined_at'] ?? ''));

                if ($joinedAt !== '' && strtotime($joinedAt) === false) {
                    throw ValidationException::withMessages([
                        'file' => "Row {$rowNumber} contains an invalid joined_at date.",
                    ]);
                }

                $rows[] = [
                    'name' => trim((string) $row['name']),
                    'email' => strtolower(trim((string) $row['email'])),
                    'role' => $role,
                    'verified' => in_array($verified, ['1', 'yes', 'true', 'verified'], true),
                    'joined_at' => $joinedAt,
                ];
            }
        } finally {
            fclose($handle);
        }

        return DB::transaction(function () use ($rows) {
            $created = 0;
            $updated = 0;

            foreach ($rows as $row) {
                $user = User::withTrashed()->where('email', $row['email'])->first();

                if ($user?->role === AccountType::SuperAdmin->value) {
                    continue;
                }

                if ($user === null) {
                    $user = new User;
                    $user->email = $row['email'];
                    $user->password = Str::random(40);
                    $created++;
                } else {
                    $updated++;
                }

                $user->name = $row['name'];
                $user->role = $row['role'];
                $user->email_verified_at = $row['verified']
                    ? ($user->email_verified_at ?? now())
                    : null;

                if (! $user->exists && $row['joined_at'] !== '') {
                    $user->created_at = $row['joined_at'];
                }

                $user->save();

                if ($user->trashed()) {
                    $user->restore();
                }
            }

            return compact('created', 'updated');
        });
    }

    public function find(int $id): User
    {
        return $this->userRepository->findWithRelations($id);
    }

    public function archive(User $user): void
    {
        abort_if(
            $user->role === AccountType::SuperAdmin->value,
            403,
            'Cannot archive super admin.',
        );
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
