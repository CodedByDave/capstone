<?php

namespace App\Services;

use App\Models\CustomerAddress;
use App\Models\User;
use App\Repositories\CustomerAddressRepository;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class CustomerAddressService
{
    /** How many addresses one customer can keep. */
    public const MAX_ADDRESSES = 2;

    public function __construct(private readonly CustomerAddressRepository $addresses) {}

    /** @return Collection<int, CustomerAddress> */
    public function listFor(User $user): Collection
    {
        return $this->addresses->forUser($user->id);
    }

    /**
     * @param  array{label: string, recipient_name: string, phone: string, address: string, latitude?: float|null, longitude?: float|null, is_default?: bool}  $data
     */
    public function create(User $user, array $data): CustomerAddress
    {
        return DB::transaction(function () use ($user, $data) {
            $existing = $this->addresses->lockForUser($user->id);

            if ($existing->count() >= self::MAX_ADDRESSES) {
                throw ValidationException::withMessages([
                    'address' => 'You can save up to '.self::MAX_ADDRESSES.' addresses. Delete one to add another.',
                ]);
            }

            // The first address is always the default; otherwise only when asked for.
            $makeDefault = $existing->isEmpty() || (bool) ($data['is_default'] ?? false);

            if ($makeDefault) {
                $this->addresses->clearDefault($user->id);
            }

            return $this->addresses->create([
                ...$this->fields($data),
                'user_id' => $user->id,
                'is_default' => $makeDefault,
            ]);
        });
    }

    public function update(User $user, string $publicId, array $data): CustomerAddress
    {
        return DB::transaction(function () use ($user, $publicId, $data) {
            $this->addresses->lockForUser($user->id);
            $address = $this->findOrFail($user, $publicId);

            $this->addresses->update($address, $this->fields($data));

            if (! empty($data['is_default']) && ! $address->is_default) {
                $this->setDefault($address);
            }

            return $address->refresh();
        });
    }

    public function makeDefault(User $user, string $publicId): CustomerAddress
    {
        return DB::transaction(function () use ($user, $publicId) {
            $this->addresses->lockForUser($user->id);
            $address = $this->findOrFail($user, $publicId);
            $this->setDefault($address);

            return $address->refresh();
        });
    }

    public function delete(User $user, string $publicId): void
    {
        DB::transaction(function () use ($user, $publicId) {
            $this->addresses->lockForUser($user->id);
            $address = $this->findOrFail($user, $publicId);
            $wasDefault = $address->is_default;

            $this->addresses->delete($address);

            // Never leave the customer with addresses but no default.
            if ($wasDefault) {
                $next = $this->addresses->forUser($user->id)->first();

                if ($next) {
                    $this->addresses->update($next, ['is_default' => true]);
                }
            }
        });
    }

    private function setDefault(CustomerAddress $address): void
    {
        $this->addresses->clearDefault($address->user_id, $address->id);
        $this->addresses->update($address, ['is_default' => true]);
    }

    private function findOrFail(User $user, string $publicId): CustomerAddress
    {
        return $this->addresses->findForUser($user->id, $publicId)
            ?? throw (new ModelNotFoundException)->setModel(CustomerAddress::class);
    }

    /** @return array<string, mixed> */
    private function fields(array $data): array
    {
        return [
            'label' => $data['label'],
            'recipient_name' => $data['recipient_name'],
            'phone' => $data['phone'],
            'address' => $data['address'],
            'latitude' => $data['latitude'] ?? null,
            'longitude' => $data['longitude'] ?? null,
        ];
    }
}
