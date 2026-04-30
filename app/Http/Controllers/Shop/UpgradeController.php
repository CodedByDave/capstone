<?php

namespace App\Http\Controllers\Shop;

use App\Http\Controllers\Controller;
use App\Http\Requests\Shop\StoreUpgradeRequest;
use App\Models\Order;
use App\Models\Payment;
use App\Models\Shop;
use App\Services\OrderService;
use App\Services\PaymentService;
use App\Services\PaymongoService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;
use Inertia\Response;

class UpgradeController extends Controller
{
    private const PLAN_RANK = ['Basic' => 1, 'Standard' => 2, 'Premium' => 3];

    public function __construct(
        protected OrderService   $orderService,
        protected PaymentService $paymentService,
        protected PaymongoService $paymongoService
    ) {}

    // ── Show upgrade plan selection ────────────────────────────────────────────

    public function show(): Response
    {
        $user = auth()->user();

        $currentOrder = Order::where('user_id', $user->id)
            ->whereNotIn('status', ['rejected'])
            ->latest()
            ->first();

        $currentPlan = $currentOrder?->plan_name ?? null;
        $currentRank = self::PLAN_RANK[$currentPlan ?? ''] ?? 0;
        $isExpired   = $currentOrder?->status === 'expired';
        $isTrial     = (bool) ($currentOrder?->is_trial ?? false);

        // Trial (expired or active): all plans available — user is choosing their first paid plan
        // Expired paid plan: allow renewing same plan (rank >=) or upgrading
        // Active paid plan: only allow upgrading to a strictly higher plan (rank >)
        $availableUpgrades = array_keys(
            array_filter(self::PLAN_RANK, fn($rank) => ($isTrial || $isExpired && $isTrial)
                ? true                    // trial → any plan
                : ($isExpired
                    ? $rank >= $currentRank   // renew same or upgrade
                    : $rank > $currentRank    // upgrade only
                )
            )
        );

        return Inertia::render('shop/Upgrade', [
            'currentPlan'       => $currentPlan,
            'currentExpiresAt'  => $currentOrder?->expires_at,
            'availableUpgrades' => $availableUpgrades,
            'isExpired'         => $isExpired,
            'isTrial'           => $isTrial,
            'vatPct'            => 12,
        ]);
    }

    // ── Store plan selection in session ────────────────────────────────────────

    public function select(Request $request): RedirectResponse
    {
        $request->validate([
            'plan_name'      => 'required|string|in:Basic,Standard,Premium',
            'billing_months' => 'required|integer|in:1,12,24,48',
            'discount_pct'   => 'required|integer',
            'monthly_price'  => 'required|integer',
            'total_amount'   => 'required|integer',
        ]);

        $user         = auth()->user();
        $currentOrder = Order::where('user_id', $user->id)
            ->whereNotIn('status', ['rejected'])
            ->latest()
            ->first();

        $isExpired    = $currentOrder?->status === 'expired';
        $isTrial      = (bool) ($currentOrder?->is_trial ?? false);
        $currentRank  = self::PLAN_RANK[$currentOrder?->plan_name ?? ''] ?? 0;
        $selectedRank = self::PLAN_RANK[$request->plan_name] ?? 0;

        // Trial → any plan is valid. Expired → same or higher. Active → strictly higher.
        $invalid = $isTrial ? false : ($isExpired ? $selectedRank < $currentRank : $selectedRank <= $currentRank);

        if ($invalid) {
            return back()->with('toast', [
                'type'    => 'error',
                'message' => 'You can only upgrade to a higher plan.',
            ]);
        }

        session([
            'upgrade_checkout' => [
                'plan_name'      => $request->plan_name,
                'billing_months' => $request->billing_months,
                'discount_pct'   => $request->discount_pct,
                'monthly_price'  => $request->monthly_price,
                'total_amount'   => $request->total_amount,
            ]
        ]);

        return redirect()->route('shop.upgrade.confirm');
    }

    // ── Confirm page ───────────────────────────────────────────────────────────

    public function confirm(): Response|RedirectResponse
    {
        // If payment was just processed, forward to PayMongo
        if (session('upgrade_checkout_url')) {
            return Inertia::render('shop/UpgradeConfirm', [
                'planName'      => session('upgrade_plan_name', 'Standard'),
                'billingMonths' => session('upgrade_billing_months', 1),
                'vatPct'        => 12,
                'checkoutUrl'   => session('upgrade_checkout_url'),
            ]);
        }

        $checkout = session('upgrade_checkout');

        if (! $checkout) {
            return redirect()->route('shop.upgrade')->with('toast', [
                'type'    => 'error',
                'message' => 'No upgrade plan selected.',
            ]);
        }

        $user = auth()->user();
        $shop = Shop::where('owner_id', $user->id)->first();

        return Inertia::render('shop/UpgradeConfirm', [
            'planName'      => $checkout['plan_name'],
            'billingMonths' => (int) $checkout['billing_months'],
            'vatPct'        => 12,
            'checkoutUrl'   => null,
            'user' => [
                'name'  => $user->name,
                'email' => $user->email,
            ],
            'shop' => $shop ? [
                'shop_name'    => $shop->shop_name,
                'phone'        => $shop->phone ?? '',
                'block_street' => $shop->block_street ?? '',
                'municipality' => $shop->municipality ?? '',
                'barangay'     => $shop->barangay ?? '',
                'postal_code'  => $shop->postal_code ?? '',
            ] : null,
        ]);
    }

    // ── Process upgrade order ──────────────────────────────────────────────────

    public function process(StoreUpgradeRequest $request): RedirectResponse
    {
        try {
            $user         = auth()->user();
            $currentOrder = Order::where('user_id', $user->id)
                ->whereNotIn('status', ['rejected'])
                ->latest()
                ->first();

            $planName      = $request->validated()['plan_name'];
            $billingMonths = (int) $request->validated()['billing_months'];

            $isExpired    = $currentOrder?->status === 'expired';
            $isTrial      = (bool) ($currentOrder?->is_trial ?? false);
            $currentRank  = self::PLAN_RANK[$currentOrder?->plan_name ?? ''] ?? 0;
            $selectedRank = self::PLAN_RANK[$planName] ?? 0;

            $invalid = $isTrial ? false : ($isExpired ? $selectedRank < $currentRank : $selectedRank <= $currentRank);

            if ($invalid) {
                return back()->with('toast', [
                    'type'    => 'error',
                    'message' => 'You can only upgrade to a higher plan.',
                ]);
            }

            $planPrices   = ['Basic' => 3800, 'Standard' => 6300, 'Premium' => 8000];
            $discounts    = [1 => 0, 12 => 10, 24 => 20, 48 => 30];
            $basePrice    = $planPrices[$planName];
            $discountPct  = $discounts[$billingMonths] ?? 0;
            $monthlyPrice = $basePrice * (1 - $discountPct / 100);
            $subtotal     = $monthlyPrice * $billingMonths;
            $vatAmount    = round($subtotal * 0.12);
            $grandTotal   = $subtotal + $vatAmount;

            $shop = Shop::where('owner_id', $user->id)->firstOrFail();

            [$order, $payment] = DB::transaction(function () use ($request, $user, $shop, $planName, $billingMonths, $grandTotal) {
                $order = $this->orderService->create([
                    'shop_name'      => $shop->shop_name,
                    'phone'          => $shop->phone ?? '',
                    'block_street'   => $shop->block_street ?? '',
                    'municipality'   => $shop->municipality ?? '',
                    'barangay'       => $shop->barangay ?? '',
                    'postal_code'    => $shop->postal_code ?? '',
                    'owner_name'     => $user->name,
                    'email'          => $user->email,
                    'user_id'        => $user->id,
                    'plan_name'      => $planName,
                    'billing_months' => $billingMonths,
                    'total_price'    => $grandTotal,
                    'payment_method' => $request->validated()['payment_method'],
                ]);

                // Mark this as an upgrade order
                $order->update(['is_upgrade' => true]);

                $payment = $this->paymentService->createForOrder($order, [
                    'payment_method' => $request->validated()['payment_method'],
                    'amount'         => $grandTotal,
                ]);

                return [$order, $payment];
            });

            $session = $this->paymongoService->createCheckoutSession($order);

            Payment::where('order_id', $order->id)->update([
                'paymongo_session_id' => $session['data']['id'],
            ]);

            session()->forget('upgrade_checkout');
            session([
                'upgrade_checkout_url'     => $session['data']['attributes']['checkout_url'],
                'upgrade_plan_name'        => $planName,
                'upgrade_billing_months'   => $billingMonths,
                'pending_order_id'         => $order->id,
            ]);

            return redirect()->route('shop.upgrade.confirm');
        } catch (\Exception $e) {
            Log::error('=== UPGRADE FAILED ===', [
                'error' => $e->getMessage(),
                'file'  => $e->getFile(),
                'line'  => $e->getLine(),
            ]);

            return back()->with('toast', [
                'type'    => 'error',
                'message' => 'Something went wrong. Please try again.',
            ]);
        }
    }
}
