<?php

namespace App\Http\Controllers\Shop;

use App\Http\Controllers\Controller;
use App\Http\Requests\Shop\StoreOrderRequest;
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

class CheckoutController extends Controller
{
    public function __construct(
        protected OrderService    $orderService,
        protected PaymentService  $paymentService,
        protected PaymongoService $paymongoService
    ) {}

    // ── Plan selection page ────────────────────────────────────────────────────

    public function show(string $plan): Response|RedirectResponse
    {
        return Inertia::render('shop/Checkout', [
            'planName' => $plan,
            'vatPct'   => 12,
            'user'     => auth()->user() ? [
                'name'  => auth()->user()->name,
                'email' => auth()->user()->email,
            ] : null,
        ]);
    }

    public function plans(): Response|RedirectResponse
    {
        $hasActiveOrder = Order::where('user_id', auth()->id())
            ->whereIn('status', ['approved', 'paid', 'pending'])
            ->exists();

        if ($hasActiveOrder) {
            return redirect()->route('shop.dashboard')->with('toast', [
                'type'    => 'error',
                'message' => 'You already have an active plan.',
            ]);
        }

        return Inertia::render('Landing');
    }

    // ── Store selected plan + billing period in session ────────────────────────

    public function select(Request $request): RedirectResponse
    {
        $request->validate([
            'plan_name'      => 'required|string|in:Basic,Standard,Premium',
            'billing_months' => 'required|integer|in:1,12,24,48',
            'discount_pct'   => 'required|integer',
            'monthly_price'  => 'required|integer',
            'total_amount'   => 'required|integer',
        ]);

        if (auth()->check()) {
            $hasActiveOrder = Order::where('user_id', auth()->id())
                ->whereIn('status', ['approved', 'paid', 'pending'])
                ->exists();

            if ($hasActiveOrder) {
                return redirect()->route('shop.dashboard')->with('toast', [
                    'type'    => 'error',
                    'message' => 'You already have an active plan.',
                ]);
            }
        }

        session([
            'checkout' => [
                'plan_name'      => $request->plan_name,
                'billing_months' => $request->billing_months,
                'discount_pct'   => $request->discount_pct,
                'monthly_price'  => $request->monthly_price,
                'total_amount'   => $request->total_amount,
            ]
        ]);

        if (auth()->check()) {
            return redirect()->route('checkout.confirm');
        }

        return redirect()->route('login')->with('toast', [
            'type'    => 'info',
            'message' => 'Please log in or create an account to continue.',
        ]);
    }

    // Confirm page (Authenticated Users)
    public function confirm(): Response|RedirectResponse
    {
        $checkout = session('checkout');

        if (!$checkout && !session('checkout_url')) {
            return redirect()->route('landing')->with('toast', [
                'type'    => 'error',
                'message' => 'No plan selected. Please pick a plan first.',
            ]);
        }

        $user = auth()->user();
        $shop = Shop::where('owner_id', $user->id)->first();

        $billingMonths = (int) ($checkout['billing_months'] ?? 12);

        $municipalityMap = [
            'Cavite City'    => 'City of Cavite',
            'Dasmariñas'     => 'City of Dasmariñas',
            'Bacoor'         => 'City of Bacoor',
            'Imus'           => 'City of Imus',
            'Trece Martires' => 'City of Trece Martires',
            'General Trias'  => 'City of General Trias',
        ];

        $savedMunicipality = $shop?->municipality ?? '';
        $mappedMunicipality = $municipalityMap[$savedMunicipality] ?? $savedMunicipality;

        return Inertia::render('shop/CheckoutConfirm', [
            'planName'      => $checkout['plan_name'] ?? 'Standard',
            'billingMonths' => $billingMonths,
            'vatPct'        => 12,
            'user'          => [
                'name'         => $user->name,
                'email'        => $user->email,
                'phone'        => $shop?->phone        ?? '',
                'shop_name'    => $shop?->shop_name    ?? '',
                'block_street' => $shop?->block_street ?? '',
                'municipality' => $mappedMunicipality,
                'barangay'     => $shop?->barangay     ?? '',
                'postal_code'  => $shop?->postal_code  ?? '',
            ],
        ]);
    }

    // ── Process order ──────────────────────────────────────────────────────────

    public function checkout(StoreOrderRequest $request): RedirectResponse
    {
        Log::info('CHECKOUT HIT', $request->all());

        try {
            Log::info('=== CHECKOUT START ===', $request->validated());

            $user = auth()->user();

            //Block duplicate orders
            $hasActiveOrder = Order::where('user_id', $user->id)
                ->whereIn('status', ['approved', 'paid', 'pending'])
                ->exists();

            if ($hasActiveOrder) {
                Log::warning('Duplicate order attempt blocked', ['user_id' => $user->id]);

                return back()->with('toast', [
                    'type'    => 'error',
                    'message' => 'You already have an active plan or pending order.',
                ]);
            }

            $planName      = $request->validated()['plan_name'];
            $billingMonths = (int) $request->validated()['billing_months'];

            $planPrices = ['Basic' => 3800, 'Standard' => 6300, 'Premium' => 8000];
            $discounts  = [1 => 0, 12 => 10, 24 => 20, 48 => 30];

            $basePrice    = $planPrices[$planName];
            $discountPct  = $discounts[$billingMonths] ?? 0;
            $monthlyPrice = $basePrice * (1 - $discountPct / 100);
            $subtotal     = $monthlyPrice * $billingMonths;
            $vatAmount    = round($subtotal * 0.12);
            $grandTotal   = $subtotal + $vatAmount;

            [$order, $payment] = DB::transaction(function () use ($request, $user, $planName, $billingMonths, $grandTotal) {

                $order = $this->orderService->create([
                    'shop_name'      => $request->validated()['shop_name'],
                    'phone'          => $request->validated()['phone'],
                    'block_street'   => $request->validated()['block_street'],
                    'municipality'   => $request->validated()['municipality'],
                    'barangay'       => $request->validated()['barangay'],
                    'postal_code'    => $request->validated()['postal_code'],
                    'owner_name'     => $user->name,
                    'email'          => $user->email,
                    'user_id'        => $user->id,
                    'plan_name'      => $planName,
                    'billing_months' => $billingMonths,
                    'total_price'    => $grandTotal,
                    'payment_method' => $request->validated()['payment_method'],
                ]);

                Log::info('Order created', [
                    'order_id'    => $order->id,
                    'total_price' => $order->total_price,
                ]);

                $payment = $this->paymentService->createForOrder($order, [
                    'payment_method' => $request->validated()['payment_method'],
                    'amount'         => $grandTotal,
                ]);

                Log::info('Payment record created', ['payment_id' => $payment->id]);

                return [$order, $payment];
            });

            $kycPaths = [];
            foreach (['kyc_bir', 'kyc_dti', 'kyc_mayors', 'kyc_sanitary'] as $key) {
                if ($request->hasFile($key)) {
                    $kycPaths[$key] = $request->file($key)->store(
                        "kyc/{$order->id}",
                        'private'
                    );
                }
            }

            if (!empty($kycPaths)) {
                Order::where('id', $order->id)->update($kycPaths);
                Log::info('KYC files stored', ['order_id' => $order->id, 'files' => $kycPaths]);
            }

            $order = Order::findOrFail($order->id);

            $session = $this->paymongoService->createCheckoutSession($order);

            Payment::where('order_id', $order->id)->update([
                'paymongo_session_id' => $session['data']['id'],
            ]);

            Log::info('Checkout session created', [
                'session_id'   => $session['data']['id'],
                'checkout_url' => $session['data']['attributes']['checkout_url'],
            ]);

            session()->forget('checkout');
            session(['checkout_url' => $session['data']['attributes']['checkout_url']]);

            return redirect()->route('checkout.confirm');
        } catch (\Exception $e) {
            Log::error('=== CHECKOUT FAILED ===', [
                'error' => $e->getMessage(),
                'file'  => $e->getFile(),
                'line'  => $e->getLine(),
                'trace' => $e->getTraceAsString(),
            ]);

            return back()->with('toast', [
                'type'    => 'error',
                'message' => 'Something went wrong. Please try again.',
            ]);
        }
    }

    // ── Payment success ────────────────────────────────────────────────────────

    public function success(Request $request): Response
    {
        $orderId = $request->query('order_id');

        if ($orderId) {
            $order   = Order::with('modules')->find($orderId);
            $payment = Payment::where('order_id', $orderId)->first();

            if ($payment && $payment->paymongo_session_id) {
                try {
                    $session       = $this->paymongoService->getCheckoutSession($payment->paymongo_session_id);
                    $sessionStatus = $session['data']['attributes']['status'] ?? null;
                    $paymentStatus = $session['data']['attributes']['payment_intent']['attributes']['status'] ?? null;

                    if (
                        in_array($sessionStatus, ['completed', 'paid']) ||
                        in_array($paymentStatus, ['paid', 'succeeded'])
                    ) {
                        $payment->update([
                            'status'  => 'paid',
                            'paid_at' => now(),
                        ]);

                        $order?->update(['status' => 'paid']);

                        $payment = $payment->fresh();
                        $order   = $order->fresh()->load('modules');
                    }
                } catch (\Exception $e) {
                    Log::error('Failed to verify PayMongo session on success', [
                        'order_id' => $orderId,
                        'error'    => $e->getMessage(),
                    ]);
                }
            }

            return Inertia::render('shop/payment/PaymentSuccess', [
                'order'   => $order ? [
                    'id'           => $order->id,
                    'status'       => $order->status,
                    'shop_name'    => $order->shop_name,
                    'owner_name'   => $order->owner_name,
                    'email'        => $order->email,
                    'phone'        => $order->phone,
                    'block_street' => $order->block_street,
                    'municipality' => $order->municipality,
                    'barangay'     => $order->barangay,
                    'postal_code'  => $order->postal_code,
                    'total_price'  => $order->total_price,
                    'created_at'   => $order->created_at,
                    'modules'      => $order->modules->map(fn($m) => [
                        'name'  => $m->name,
                        'price' => $m->price,
                    ]),
                ] : null,
                'payment' => $payment,
            ]);
        }

        return Inertia::render('shop/payment/PaymentSuccess');
    }

    // ── Payment cancel
    public function cancel(): Response
    {
        return Inertia::render('shop/payment/PaymentCancel');
    }
}
