<?php

namespace App\Services;

use App\Models\Shop;
use App\Models\User;
use App\Repositories\ShopRepository;
use Illuminate\Database\Eloquent\ModelNotFoundException;

/**
 * A shop owner's operating hours: the weekdays the shop opens, and either all day or only between two times.
 */
class ShopOperatingHoursService
{
    public function __construct(private readonly ShopRepository $shops) {}

    public function pageData(User $owner): array
    {
        $shop = $this->shopForOwner($owner);

        return [
            'is_open_24_7' => (bool) $shop->is_open_24_7,
            'opens_at' => $shop->opens_at ? substr($shop->opens_at, 0, 5) : '08:00',
            'closes_at' => $shop->closes_at ? substr($shop->closes_at, 0, 5) : '17:00',
            'operating_days' => $shop->openDays(),
            'daily_hours' => $shop->dailyHoursMap(),
        ];
    }

    public function update(User $owner, array $data): Shop
    {
        $shop = $this->shopForOwner($owner);
        $always = (bool) $data['is_open_24_7'];
        $days = collect($data['operating_days'] ?? $shop->openDays())->map(fn ($day) => (int) $day)->unique()->sort()->values()->all();

        // Per-day hours, kept only for days the shop opens.
        $daily = collect($data['daily_hours'] ?? $shop->daily_hours ?? [])
            ->filter(fn ($hours, $day) => in_array((int) $day, $days, true))
            ->map(fn ($hours) => ['opens_at' => $hours['opens_at'], 'closes_at' => $hours['closes_at']])
            ->all();

        // opens_at / closes_at stay as the shop-wide fallback: the first open day's hours.
        $first = $daily[$days[0] ?? -1] ?? null;
        $opensAt = $first['opens_at'] ?? $data['opens_at'] ?? null;
        $closesAt = $first['closes_at'] ?? $data['closes_at'] ?? null;

        return $this->shops->updateDeliveryPricing($shop, [
            'is_open_24_7' => $always,
            // Keep the last custom hours when switching to 24/7, so switching back needs no retyping.
            'opens_at' => $always ? $shop->opens_at : $opensAt,
            'closes_at' => $always ? $shop->closes_at : $closesAt,
            'operating_days' => $days,
            'daily_hours' => $always ? ($shop->daily_hours ?? $daily) : $daily,
        ]);
    }

    private function shopForOwner(User $owner): Shop
    {
        return $this->shops->findByOwnerId($owner->id)
            ?? throw (new ModelNotFoundException)->setModel(Shop::class);
    }
}
