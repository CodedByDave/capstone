<?php

namespace App\Services;

use App\DTOs\TableSort;
use App\Enums\AccountType;
use App\Exceptions\SupplierAccountException;
use App\Mail\SupplierInvitationMail;
use App\Models\Supplier;
use App\Models\User;
use App\Repositories\EmployeeRepository;
use App\Repositories\ShopRepository;
use App\Repositories\SupplierRepository;
use App\Repositories\UserRepository;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Throwable;

class SupplierService
{
    public function __construct(
        private readonly SupplierRepository $supplierRepository,
        private readonly UserRepository $users,
        private readonly ShopRepository $shops,
        private readonly EmployeeRepository $employees,
    ) {}

    /** The shop an owner or staff login manages; anyone else gets a not-found. */
    public function shopIdFor(User $user): int
    {
        $shopId = match ($user->role) {
            AccountType::ShopOwner->value => $this->shops->findByOwnerId($user->id)?->id,
            AccountType::Staff->value => $this->employees->shopIdForUser($user->id),
            default => null,
        };

        return $shopId ?? throw (new ModelNotFoundException)->setModel(Supplier::class);
    }

    public function paginate(int $shopId, ?TableSort $sort = null, int $perPage = 15): LengthAwarePaginator
    {
        return $this->supplierRepository->paginateForShop($shopId, $sort, $perPage);
    }

    public function archived(int $shopId, ?TableSort $sort = null): LengthAwarePaginator
    {
        return $this->supplierRepository->archivedForShop($shopId, $sort);
    }

    public function allActive(int $shopId): Collection
    {
        return $this->supplierRepository->allActiveForShop($shopId);
    }

    public function find(string $publicId, int $shopId): Supplier
    {
        return $this->supplierRepository->findForShopOrFail($publicId, $shopId);
    }

    public function detail(string $publicId, int $shopId): Supplier
    {
        return $this->supplierRepository->detailForShopOrFail($publicId, $shopId);
    }

    /** Adds a supplier and creates the login they use for the supplier dashboard. */
    public function create(int $shopId, array $data): Supplier
    {
        $supplier = DB::transaction(function () use ($shopId, $data): Supplier {
            $supplier = $this->supplierRepository->createForShop($shopId, $data);
            $this->createAccount($supplier);

            return $supplier;
        });

        $this->sendInvitation($supplier);

        return $supplier;
    }

    public function update(Supplier $supplier, array $data): Supplier
    {
        return DB::transaction(function () use ($supplier, $data): Supplier {
            $this->supplierRepository->update($supplier, $data);

            // The supplier signs in with the email the shop keeps on file.
            $user = $supplier->user;
            if ($user !== null) {
                $email = $data['email'] ?? null;

                if (blank($email)) {
                    throw new SupplierAccountException('This supplier has a login, so an email address is required.');
                }

                if ($user->email !== $email) {
                    if ($this->users->emailTaken($email, $user->id)) {
                        throw new SupplierAccountException('Another account already uses this email address.');
                    }

                    $this->users->updateEmail($user, $email);
                }
            }

            return $supplier->fresh();
        });
    }

    /**
     * Sends a fresh set-password link, creating the login first for a supplier added before
     * supplier accounts existed.
     */
    public function inviteAccount(Supplier $supplier): void
    {
        $user = $supplier->user_id === null ? null : $this->users->findWithTrashed($supplier->user_id);

        DB::transaction(function () use ($supplier, $user): void {
            if ($user === null) {
                $this->createAccount($supplier);
            } elseif ($user->trashed()) {
                // A login that was archived is brought back rather than duplicated.
                $this->users->restoreUser($user);
            }
        });

        $this->sendInvitation($supplier->refresh(), throwOnFailure: true);
    }

    public function delete(Supplier $supplier): bool
    {
        return DB::transaction(function () use ($supplier) {
            return $this->supplierRepository->delete($supplier);
        });
    }

    public function restore(string $publicId, int $shopId): bool
    {
        return DB::transaction(function () use ($publicId, $shopId) {
            return $this->supplierRepository->restore($publicId, $shopId);
        });
    }

    private function createAccount(Supplier $supplier): User
    {
        $email = $supplier->email;

        if (blank($email)) {
            throw new SupplierAccountException('Add an email address for this supplier before creating their login.');
        }

        if ($this->users->emailTaken($email)) {
            throw new SupplierAccountException('Another account already uses this email address.');
        }

        // Nobody knows this password; the supplier sets their own from the invitation link.
        try {
            $user = $this->users->create([
                'name' => $supplier->contact_person ?: $supplier->name,
                'email' => $email,
                'password' => Hash::make(Str::random(64)),
                'role' => AccountType::Supplier->value,
                'shop_id' => $supplier->shop_id,
                'email_verified_at' => now(),
            ]);
        } catch (UniqueConstraintViolationException) {
            // Another request took the email between the check above and this insert.
            throw new SupplierAccountException('Another account already uses this email address.');
        }

        $this->supplierRepository->attachUser($supplier, $user->id);

        return $user;
    }

    private function sendInvitation(Supplier $supplier, bool $throwOnFailure = false): void
    {
        $user = $supplier->user()->first();

        if ($user === null) {
            if ($throwOnFailure) {
                throw new SupplierAccountException('This supplier has no login to send an invitation to.');
            }

            return;
        }

        try {
            $token = Password::broker()->createToken($user);

            Mail::to($user->email)->send(new SupplierInvitationMail(
                name: $user->name,
                shopName: $supplier->shop()->value('shop_name') ?? 'Your laundry shop',
                setPasswordUrl: route('password.reset', ['token' => $token, 'email' => $user->email]),
                expiresInMinutes: (int) config('auth.passwords.users.expire', 60),
            ));
        } catch (Throwable $e) {
            Log::warning('Failed to send supplier invitation email', [
                'supplier_id' => $supplier->id,
                'error' => $e->getMessage(),
            ]);

            if ($throwOnFailure) {
                throw new SupplierAccountException('The invitation email could not be sent. Please try again later.');
            }
        }
    }
}
