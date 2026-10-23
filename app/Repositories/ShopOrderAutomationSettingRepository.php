<?php

namespace App\Repositories;

use App\Models\ShopOrderAutomationSetting;
use Illuminate\Support\Collection;

class ShopOrderAutomationSettingRepository extends Repository
{
    public function __construct(ShopOrderAutomationSetting $model)
    {
        parent::__construct($model);
    }

    public function findForShop(int $shopId): ?ShopOrderAutomationSetting
    {
        return ShopOrderAutomationSetting::query()->where('shop_id', $shopId)->first();
    }

    /** The shop's saved setting, or an unsaved one with every rule off. */
    public function forShopOrDefault(int $shopId): ShopOrderAutomationSetting
    {
        return $this->findForShop($shopId)
            ?? new ShopOrderAutomationSetting(['shop_id' => $shopId] + ShopOrderAutomationSetting::defaults());
    }

    public function saveForShop(int $shopId, array $attributes): ShopOrderAutomationSetting
    {
        return ShopOrderAutomationSetting::query()->updateOrCreate(['shop_id' => $shopId], $attributes);
    }

    /**
     * Settings with a time-based rule switched on, for the scheduled job.
     *
     * @return Collection<int, ShopOrderAutomationSetting>
     */
    public function withTimeRulesEnabled(): Collection
    {
        return ShopOrderAutomationSetting::query()
            ->where(fn ($query) => $query->where('auto_complete', true)->orWhere('remind_overdue', true))
            ->get();
    }
}
