<?php

namespace App\Services;

use App\Models\ShopOrderAutomationSetting;
use App\Models\User;
use App\Repositories\ShopOrderAutomationSettingRepository;
use Illuminate\Support\Facades\DB;

/** The shop owner's page for choosing which order status changes happen automatically. */
class OrderAutomationSettingsService
{
    public function __construct(
        private readonly ShopOrderAutomationSettingRepository $settings,
        private readonly ShopContext $shopContext,
        private readonly ShopSetupService $setup,
        private readonly ActivityLogService $activityLog,
    ) {}

    /** @return array{settings: array<string, mixed>, available: bool, limits: array{max_grace_minutes: int}} */
    public function pageData(User $owner): array
    {
        $shopId = $this->shopContext->shopIdFor($owner);
        $setting = $this->settings->forShopOrDefault($shopId);

        return [
            'settings' => $setting->only([
                'start_on_weigh_in',
                'start_on_payment',
                'auto_complete',
                'auto_complete_grace_minutes',
                'remind_overdue',
            ]),
            // False on a free trial or an expired plan: the page opens but is locked.
            'available' => $this->setup->shopSupports($shopId, OrderStatusAutomationService::CAPABILITY),
            'limits' => ['max_grace_minutes' => ShopOrderAutomationSetting::MAX_GRACE_MINUTES],
        ];
    }

    public function update(User $owner, array $attributes): ShopOrderAutomationSetting
    {
        $shopId = $this->shopContext->shopIdFor($owner);

        return DB::transaction(function () use ($shopId, $attributes): ShopOrderAutomationSetting {
            $setting = $this->settings->saveForShop($shopId, $attributes);

            $this->activityLog->log($setting, 'Updated order automation settings', $attributes, $shopId, 'Operations');

            return $setting;
        });
    }
}
