<?php

namespace App\Http\Controllers\Shop;

use App\Exceptions\OrderSubmissionException;
use App\Http\Controllers\Controller;
use App\Http\Requests\Shop\SelectPlanRequest;
use App\Http\Requests\Shop\StoreOrderRequest;
use App\Http\Requests\Shop\StorePaymentRequest;
use App\Models\Order;
use App\Services\BusinessAgreementService;
use App\Services\OrderService;
use App\Services\PaymentService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;

class CheckoutController extends Controller
{
    public function __construct(
        protected OrderService $orderService,
        protected PaymentService $paymentService,
        protected BusinessAgreementService $businessAgreementService,
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

        return Inertia::render('Landing');
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
            return redirect()->route('checkout.confirm');
        }

        return redirect()->route('login')->with('toast', [
            'type' => 'info',
            'message' => 'Please log in or create an account to continue.',
        ]);
    }

    // ── Confirm page (Authenticated Users) ────────────────────────────────────

    public function confirm(): Response|RedirectResponse
    {
        $checkout = session('checkout');

        if (! $checkout && ! session('checkout_url')) {
            return redirect()->route('landing')->with('toast', [
                'type' => 'error',
                'message' => 'No plan selected. Please pick a plan first.',
            ]);
        }

        return Inertia::render('shop/CheckoutConfirm', array_merge(
            $this->orderService->checkoutConfirmationData(auth()->user(), $checkout ?? []),
            ['agreement' => $this->businessAgreementService->currentAgreementData(auth()->user())],
        ));
    }

    // ── Process order (no payment — admin reviews first) ──────────────────────

    public function checkout(StoreOrderRequest $request): RedirectResponse|SymfonyResponse
    {
        try {
            $this->orderService->submitApplication(
                $request->user(),
                $request->validated(),
                $request->ip(),
                $request->userAgent(),
            );

            session()->flash('toast', [
                'type' => 'success',
                'message' => 'Order placed! Please wait while our admin reviews your application.',
            ]);

            // Force a full browser navigation so the Dashboard component remounts
            // with fresh props (pending_order). An XHR-based redirect would keep
            // the same component instance alive and leave the stale props in place.
            return Inertia::location(route('shop.dashboard'));
        } catch (OrderSubmissionException $exception) {
            return back()->with('toast', [
                'type' => 'error',
                'message' => $exception->getMessage(),
            ]);
        } catch (ValidationException $exception) {
            throw $exception;
        } catch (\Exception $e) {
            Log::error('=== CHECKOUT FAILED ===', [
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);

            return back()->with('toast', [
                'type' => 'error',
                'message' => 'Something went wrong. Please try again.',
            ]);
        }
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
