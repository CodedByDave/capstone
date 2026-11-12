<?php

namespace App\Services;

use App\Models\Employee;
use App\Models\Rider;
use App\Models\Shop;
use App\Models\User;
use App\Repositories\EmployeeRepository;
use App\Repositories\RiderRepository;
use App\Repositories\ShopRepository;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use RuntimeException;

/**
 * The vehicle, plate number and driver's license a shop records for an employee who works as a
 * rider. The license photo is encrypted at rest on the private disk, the same way KYC documents are.
 */
class RiderProfileService
{
    private const LICENSE_DIRECTORY = 'rider-licenses';

    public function __construct(
        private readonly RiderRepository $riders,
        private readonly ShopRepository $shops,
        private readonly EmployeeRepository $employees,
        private readonly KycDocumentVault $vault,
    ) {}

    /** @return list<string> the vehicle types the shop offers its riders */
    public function vehicleChoices(?Shop $shop): array
    {
        return array_values($shop?->rider_vehicle_types ?: [Shop::DEFAULT_RIDER_VEHICLE_TYPE]);
    }

    /** @return list<string> */
    public function vehicleChoicesForUser(?User $user): array
    {
        if ($user === null) {
            return $this->vehicleChoices(null);
        }

        $shop = $user->role === 'owner'
            ? $this->shops->findByOwnerId($user->id)
            : (($shopId = $this->employees->shopIdForUser($user->id)) ? $this->shops->findShopById($shopId) : null);

        return $this->vehicleChoices($shop);
    }

    public function hasLicense(?Employee $employee): bool
    {
        return $employee !== null && filled($this->riders->findByEmployeeId($employee->id)?->license_photo_path);
    }

    /** @return array{vehicle_ownership: string, vehicle_type: string|null, plate_number: string|null, has_license: bool} */
    public function formData(Employee $employee): array
    {
        $rider = $this->riders->findByEmployeeId($employee->id);

        return [
            'vehicle_ownership' => $rider?->vehicle_ownership ?: Rider::VEHICLE_SHOP,
            'vehicle_type' => $rider?->vehicle_type,
            'plate_number' => $rider?->plate_number,
            'has_license' => filled($rider?->license_photo_path),
        ];
    }

    /**
     * The rider attributes to save from the employee form. A new license photo is encrypted and
     * stored. Moving to a shop vehicle clears the plate and the license, since the shop no longer
     * needs the rider's own documents. A replaced or cleared photo is deleted only after the
     * surrounding transaction commits.
     *
     * @param  array{vehicle_ownership?: string|null, vehicle_type?: string|null, plate_number?: string|null, license_photo?: UploadedFile|null}  $details
     * @return array<string, string|null>
     */
    public function attributesFrom(Employee $employee, array $details): array
    {
        $ownership = $details['vehicle_ownership'] ?? null;

        if ($ownership === null) {
            // Not saved from the employee form (e.g. archive or restore): keep what is on record.
            return [];
        }

        $attributes = ['vehicle_ownership' => $ownership];

        if (filled($details['vehicle_type'] ?? null)) {
            $attributes['vehicle_type'] = $details['vehicle_type'];
        }

        $previous = $this->riders->findByEmployeeId($employee->id)?->license_photo_path;

        if ($ownership === Rider::VEHICLE_SHOP) {
            $this->deleteAfterCommit($previous);

            return $attributes + ['plate_number' => null, 'license_photo_path' => null];
        }

        $attributes['plate_number'] = $details['plate_number'] ?? null;
        $upload = $details['license_photo'] ?? null;

        if ($upload instanceof UploadedFile) {
            $attributes['license_photo_path'] = $this->vault->store($upload, self::LICENSE_DIRECTORY.'/'.$employee->shop_id);
            $this->deleteAfterCommit($previous);
        }

        return $attributes;
    }

    /** Removes a stored license photo once the change that drops it is saved for good. */
    private function deleteAfterCommit(?string $path): void
    {
        if (blank($path)) {
            return;
        }

        DB::afterCommit(fn () => $this->vault->delete($path));
    }

    /**
     * The decrypted license photo of an employee in the given shop. Anything outside the shop, or
     * without a license, is reported as not found.
     *
     * @return array{contents: string, mime: string, name: string}
     */
    public function licenseFile(Employee $employee, Shop $shop): array
    {
        $path = $employee->shop_id === $shop->id
            ? $this->riders->findByEmployeeId($employee->id)?->license_photo_path
            : null;

        if (blank($path) || ! $this->vault->exists($path)) {
            throw (new ModelNotFoundException)->setModel(Employee::class);
        }

        try {
            return [
                'contents' => $this->vault->contents($path),
                'mime' => $this->vault->mimeType($path),
                'name' => 'license-'.$employee->public_id.'.'.pathinfo($this->vault->displayName($path), PATHINFO_EXTENSION),
            ];
        } catch (RuntimeException $exception) {
            report($exception);

            throw (new ModelNotFoundException)->setModel(Employee::class);
        }
    }
}
