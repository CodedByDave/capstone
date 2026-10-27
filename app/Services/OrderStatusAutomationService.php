<?php

namespace App\Services;

use App\Models\ShopOrder;
use App\Models\ShopOrderAutomationSetting;
use App\Notifications\OrderNotification;
use App\Repositories\ShopOrderAutomationSettingRepository;
use App\Repositories\ShopOrderRepository;
use App\Repositories\ShopRepository;
use Carbon\CarbonInterface;
use Illuminate\Support\Facades\DB;
use Throwable;

/**
 * Moves orders through Pending, In Progress and Completed by the rules each shop switched on.
 * Only shops on a paid plan are automated, and staff can still change any status by hand.
 */
class OrderStatusAutomationService
{
    public const CAPABILITY = 'order-automation';

    public function __construct(
        private readonly ShopOrderAutomationSettingRepository $settings,
        private readonly ShopOrderRepository $orders,
        private readonly ShopRepository $shops,
        private readonly ShopSetupService $setup,
        private readonly ShopOrderService $orderService,
        private readonly ActivityLogService $activityLog,
    ) {}

    // ── Event rules ───────────────────────────────────────────────────────────

    public function onWeightConfirmed(ShopOrder $order): void
    {
        $this->startPendingOrder($order, 'start_on_weigh_in', 'the weight was confirmed');
    }

    public function onPaymentRecorded(ShopOrder $order): void
    {
        $this->startPendingOrder($order, 'start_on_payment', 'a payment was received');
    }

    // ── Time rules (scheduled) ────────────────────────────────────────────────

    /** @return array{completed: int, reminded: int, shops: int} */
    public function runScheduled(): array
    {
        $result = ['completed' => 0, 'reminded' => 0, 'shops' => 0];

        foreach ($this->settings->withTimeRulesEnabled() as $setting) {
            try {
                if (! $this->setup->shopSupports($setting->shop_id, self::CAPABILITY)) {
                    continue;
                }

                $result['shops']++;

                foreach ($this->orders->inProgressForShop($setting->shop_id) as $order) {
                    try {
                        $this->applyTimeRules($order, $setting, $result);
                    } catch (Throwable $exception) {
                        // One bad order must not stop the rest of the shop's orders.
                        report($exception);
                    }
                }
            } catch (Throwable $exception) {
                // Nor must one bad shop stop the other shops.
                report($exception);
            }
        }

        return $result;
    }

    /**
     * When the order should be ready: the shop's own estimate, else the time work started plus the
     * service's estimated hours. An estimate from before work (re)started is stale, so it is
     * ignored. Null when neither is known, so such an order is never automated.
     *
     * @param  array{completed: int, reminded: int, shops: int}  $result
     */
    private function applyTimeRules(ShopOrder $order, ShopOrderAutomationSetting $setting, array &$result): void
    {
        $due = $this->dueAt($order);

        if ($due === null || $due->isFuture()) {
            return;
        }

        // Only an order with a confirmed weight is complete enough to hand back automatically.
        if ($setting->auto_complete
            && $order->priceIsConfirmed()
            && $due->copy()->addMinutes($setting->auto_complete_grace_minutes)->lessThanOrEqualTo(now())) {
            $result['completed'] += $this->completeOrder($order) ? 1 : 0;

            return;
        }

        if ($setting->remind_overdue && $order->overdue_notified_at === null) {
            $result['reminded'] += $this->remindOverdue($order) ? 1 : 0;
        }
    }

    public function dueAt(ShopOrder $order): ?CarbonInterface
    {
        $estimate = $order->estimated_completion_at;
        $startedAt = $order->in_progress_at;

        if ($estimate !== null && ($startedAt === null || $estimate->greaterThanOrEqualTo($startedAt))) {
            return $estimate;
        }

        $hours = $order->service?->estimated_hours;

        if ($hours === null) {
            return null;
        }

        return ($startedAt ?? $order->created_at)->copy()->addHours((int) $hours);
    }

    // ── Transitions ───────────────────────────────────────────────────────────

    private function startPendingOrder(ShopOrder $order, string $rule, string $reason): void
    {
        $setting = $this->settings->findForShop($order->shop_id);

        if ($setting === null
            || ! $setting->{$rule}
            || ! $this->setup->shopSupports($order->shop_id, self::CAPABILITY)) {
            return;
        }

        DB::transaction(function () use ($order, $rule, $reason): void {
            $locked = $this->orders->lockForUpdate($order);

            // Someone may have moved it already; automation only ever moves an order forward.
            if ($locked->status !== 'pending') {
                return;
            }

            $this->orderService->updateStatus($locked, 'in_progress');
            $this->logChange($locked, 'pending', 'in_progress', $rule, "Order started automatically because {$reason}.");
        });
    }

    private function completeOrder(ShopOrder $order): bool
    {
        return DB::transaction(function () use ($order): bool {
            $locked = $this->orders->lockForUpdate($order);

            if ($locked->status !== 'in_progress') {
                return false;
            }

            $this->orderService->updateStatus($locked, 'completed');
            $this->logChange($locked, 'in_progress', 'completed', 'auto_complete', 'Order completed automatically after its due time.');

            return true;
        });
    }

    private function remindOverdue(ShopOrder $order): bool
    {
        return DB::transaction(function () use ($order): bool {
            $locked = $this->orders->lockForUpdate($order);

            if ($locked->status !== 'in_progress' || $locked->overdue_notified_at !== null) {
                return false;
            }

            $this->orders->update($locked, ['overdue_notified_at' => now()]);

            $shop = $this->shops->findShopById($locked->shop_id);
            $shop?->owner?->notify(new OrderNotification($locked, 'overdue'));

            return true;
        });
    }

    private function logChange(ShopOrder $order, string $from, string $to, string $rule, string $description): void
    {
        $this->activityLog->log(
            $order,
            "Automatic: {$description}",
            ['status' => ['from' => $from, 'to' => $to], 'rule' => $rule, 'automatic' => true],
            $order->shop_id,
            'Operations',
        );
    }
}
