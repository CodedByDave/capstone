<?php

namespace App\Http\Requests\Shop\Employee\Concerns;

use App\Models\Rider;
use App\Services\DriverRosterService;
use App\Services\RiderProfileService;
use Illuminate\Validation\Rule;

/**
 * An employee whose position is Rider (or Driver) needs the vehicle type they use and whether it is
 * a shop vehicle or their own. Only an own vehicle also needs its plate number and a photo of the
 * rider's driver's license. Other positions skip these fields.
 */
trait ValidatesRiderDetails
{
    protected function isRiderPosition(): bool
    {
        return DriverRosterService::isDriverPosition($this->input('position'));
    }

    protected function prepareRiderDetails(): void
    {
        if ($this->filled('plate_number')) {
            $this->merge([
                'plate_number' => mb_strtoupper(preg_replace('/\s+/', ' ', trim((string) $this->input('plate_number')))),
            ]);
        }
    }

    protected function usesOwnVehicle(): bool
    {
        return $this->input('vehicle_ownership') === Rider::VEHICLE_OWN;
    }

    /**
     * @param  bool  $licenseOnFile  the rider already has a license photo saved (edit only)
     * @return array<string, mixed>
     */
    protected function riderDetailRules(bool $licenseOnFile = false): array
    {
        if (! $this->isRiderPosition()) {
            // Not a rider: the fields are ignored and never reach the service.
            return [];
        }

        $rules = [
            'vehicle_ownership' => ['required', Rule::in(Rider::VEHICLE_OWNERSHIPS)],
            'vehicle_type' => ['required', 'string', Rule::in(app(RiderProfileService::class)->vehicleChoicesForUser($this->user()))],
        ];

        if (! $this->usesOwnVehicle()) {
            // A shop vehicle: plate number and license are not asked for.
            return $rules;
        }

        return $rules + [
            'plate_number' => ['required', 'string', 'max:12', 'regex:/^[A-Z0-9][A-Z0-9 -]{1,11}$/'],
            'license_photo' => [$licenseOnFile ? 'nullable' : 'required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
        ];
    }

    /** @return array<string, string> */
    protected function riderDetailMessages(): array
    {
        return [
            'vehicle_ownership.required' => 'Choose whether the rider uses a shop vehicle or their own.',
            'vehicle_ownership.in' => 'Choose whether the rider uses a shop vehicle or their own.',
            'vehicle_type.required' => 'Select the vehicle this rider uses.',
            'vehicle_type.in' => 'Select one of the vehicle types set in Settings.',
            'plate_number.required' => 'Plate number is required when the rider uses their own vehicle.',
            'plate_number.max' => 'Plate number can be at most 12 characters.',
            'plate_number.regex' => 'Use only letters, numbers, spaces and dashes (e.g. ABC 1234).',
            'license_photo.required' => "Upload a photo of the rider's driver's license.",
            'license_photo.image' => 'The license must be a photo (JPG, PNG or WebP).',
            'license_photo.mimes' => 'The license must be a JPG, PNG or WebP photo.',
            'license_photo.max' => 'The license photo must be 5 MB or smaller.',
        ];
    }
}
