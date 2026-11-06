<?php

namespace App\Http\Controllers\Shop;

use App\Http\Controllers\Controller;
use App\Http\Requests\Shop\SelectPlanRequest;
use App\Http\Requests\Shop\StorePaymentRequest;
use App\Models\Order;
use App\Services\BusinessAgreementService;
use App\Services\OrderService;
use App\Services\PaymentService;
use App\Services\ShopService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;
use Inertia\Response;

class CheckoutController extends Controller
{
    public function __construct(
        protected OrderService $orderService,
        protected PaymentService $paymentService,
        protected BusinessAgreementService $businessAgreementService,
        protected ShopService $shopService,
    ) {}

    // ── Plan selection page ────────────────────────────────────────────────────

    public function show(string $plan): Response|RedirectResponse
    {
        return Inertia::render('shop/Checkout', [
            'planName' => $plan,
            'vatPct' => 12,
            'user' => auth()->user() ? [
                'name' => auth()->user()->name,
                'email' => auth()->user()->email,
            ] : null,
        ]);
    }

    public function plans(): Response|RedirectResponse
    {
        if (auth()->check() && $this->orderService->hasOpenApplication(auth()->id())) {
            return redirect()->route('shop.dashboard')->with('toast', [
                'type' => 'error',
                'message' => 'You already have an active plan.',
            ]);
        }

        return Inertia::render('Landing', [
            'shopCount' => $this->shopService->countActiveShops(),
        ]);
    }

    // ── Store selected plan + billing period in session ────────────────────────

    public function select(SelectPlanRequest $request): RedirectResponse
    {
        if ($request->user() && $this->orderService->hasOpenApplication($request->user()->id)) {
            return redirect()->route('shop.dashboard')->with('toast', [
                'type' => 'error',
                'message' => 'You already have an active plan.',
            ]);
        }

        session(['checkout' => $request->validated()]);

        if (auth()->check()) {
            return redirect()->route('shop.upgrade');
        }

        return redirect()->route('login')->with('toast', [
            'type' => 'info',
            'message' => 'Please log in or create an account to continue.',
        ]);
    }

    // ── Confirm page (Authenticated Users) ────────────────────────────────────

    public function confirm(): Response|RedirectResponse
    {
        if (auth()->user()->isOwner() && ! $this->orderService->isKycVerified(auth()->id())) {
            return redirect()->route('shop.dashboard')->with('toast', [
                'type' => 'info',
                'message' => 'Verify your business documents first, then choose a plan.',
            ]);
        }

        // A plan is bought from Upgrade Plan: paid online, active as soon as the payment is verified.
        return redirect()->route('shop.upgrade');
    }

    // ── Plans are bought from Upgrade Plan; there is no application to review ─────

    public function checkout(): RedirectResponse
    {
        return redirect()->route('shop.upgrade')->with('toast', [
            'type' => 'info',
            'message' => 'Choose your plan here. It starts as soon as your payment is confirmed.',
        ]);
    }

    // ── Initiate payment for an admin-approved order ───────────────────────────

    public function pay(StorePaymentRequest $request): RedirectResponse
    {
        try {
            $result = $this->paymentService->initiateApprovedOrderPayment(
                $request->user(),
                $request->validated('payment_method'),
            );
            session(['pending_order_id' => $result['order_id']]);

            return redirect()->away($result['checkout_url']);
        } catch (\Exception $e) {
            Log::error('=== PAY INITIATION FAILED ===', [
                'error' => $e->getMessage(),
            ]);

            return back()->with('toast', [
                'type' => 'error',
                'message' => 'Could not initiate payment. Please try again.',
            ]);
        }
    }

    // ── Payment success ────────────────────────────────────────────────────────

    public function success(Order $order): Response
    {
        session()->forget('pending_order_id');

        return Inertia::render('shop/payment/PaymentSuccess', [
            'order' => $this->paymentService->verifiedReceipt(auth()->user(), $order),
        ]);
    }

    // ── Payment cancel ────────────────────────────────────────────────────────

    public function cancel(): Response
    {
        return Inertia::render('shop/payment/PaymentCancel');
    }
}
