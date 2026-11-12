<?php

namespace App\Http\Requests\Shop\Operations\Concerns;

use App\Models\ShopService;

/** Up to three photos per service, each a JPG, PNG or WebP of 5 MB or less. */
trait ValidatesServicePhotos
{
    /** @return array<string, array<int, string>> */
    protected function photoRules(bool $editing = false): array
    {
        $rules = [
            'photos' => ['nullable', 'array', 'max:'.ShopService::MAX_PHOTOS],
            'photos.*' => ['image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
        ];

        if ($editing) {
            // Positions (0 = cover) of the saved photos to take off the service.
            $rules['remove_photos'] = ['nullable', 'array', 'max:'.ShopService::MAX_PHOTOS];
            $rules['remove_photos.*'] = ['integer', 'distinct', 'min:0', 'max:'.(ShopService::MAX_PHOTOS - 1)];
        }

        return $rules;
    }

    /** @return array<string, string> */
    protected function photoMessages(): array
    {
        $max = ShopService::MAX_PHOTOS;

        return [
            'photos.array' => 'The photos could not be read. Please choose them again.',
            'photos.max' => "A service can have at most {$max} photos.",
            'photos.*.image' => 'Each service photo must be an image.',
            'photos.*.mimes' => 'Each service photo must be a JPG, PNG, or WebP image.',
            'photos.*.max' => 'Each service photo must be 5 MB or smaller.',
        ];
    }
}
