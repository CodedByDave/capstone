<?php

namespace App\Http\Controllers\User;

use App\Exceptions\ShopOrderPaymentException;
use App\Http\Controllers\Controller;
use App\Http\Requests\User\CompleteDemoPaymentRequest;
use App\Http\Requests\User\InitiateOnlinePaymentRequest;
use App\Http\Requests\User\VerifyOnlinePaymentRequest;
use App\Services\ShopOrderPaymentService;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;
use Inertia\Response;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;

class UserPaymentController extends Controller
{
    public function __construct(private readonly ShopOrderPaymentService $paymentService) {}

    public function pay(InitiateOnlinePaymentRequest $request, string $paymentPublicId): RedirectResponse|SymfonyResponse
    {
        try {
            return Inertia::location($this->paymentService->initiate(
                $request->user(),
                $paymentPublicId,
                $request->validated('payment_method'),
                $request->filled('amount') ? (float) $request->validated('amount') : null,
            ));
        } catch (ShopOrderPaymentException $exception) {
            return back()->with('toast', ['type' => 'error', 'message' => $exception->getMessage()]);
        } catch (ModelNotFoundException $exception) {
            throw $exception;
        } catch (ConnectionException $exception) {
            Log::warning('PayMongo connection timed out', [
                'payment_public_id' => $paymentPublicId,
                'error' => $exception->getMessage(),
            ]);

            return back()->with('toast', [
                'type' => 'error',
                'message' => 'PayMongo could not be reached. Check your connection and try again.',
            ]);
        } catch (\Throwable $exception) {
            Log::error('ShopOrder payment initiation failed', [
                'payment_public_id' => $paymentPublicId,
                'error' => $exception->getMessage(),
            ]);

            return back()->with('toast', ['type' => 'error', 'message' => 'Could not initiate payment. Please try again.']);
        }
    }

    public function demo(Request $request, string $paymentPublicId): Response|RedirectResponse
    {
        try {
            return Inertia::render('user/payment/DemoCheckout', [
                'order' => $this->paymentService->demoCheckoutData($request->user(), $paymentPublicId),
            ]);
        } catch (ShopOrderPaymentException $exception) {
            return redirect()->route('user.orders.index')
                ->with('toast', ['type' => 'error', 'message' => $exception->getMessage()]);
        }
    }

    public function completeDemo(CompleteDemoPaymentRequest $request, string $paymentPublicId): RedirectResponse|SymfonyResponse
    {
        try {
            return Inertia::location($this->paymentService->initiate(
                $request->user(),
                $paymentPublicId,
                $request->validated('payment_method'),
                $request->filled('amount') ? (float) $request->validated('amount') : null,
            ));
        } catch (ShopOrderPaymentException $exception) {
            return redirect()->route('user.orders.index')
                ->with('toast', ['type' => 'error', 'message' => $exception->getMessage()]);
        }
    }

    public function success(VerifyOnlinePaymentRequest $request, string $paymentPublicId): RedirectResponse
    {
        try {
            $status = $this->paymentService->verifyPaymongoReturn($request->user(), $paymentPublicId);

            if ($status !== null) {
                return redirect()->route('user.orders.payment.receipt', $paymentPublicId)->with('toast', [
                    'type' => 'success',
                    'message' => $status === 'paid'
                        ? 'Payment successful! Your receipt is ready.'
                        : 'Payment received! You still have a balance to pay on this order.',
                ]);
            }

            return redirect()->route('user.orders.index')->with('toast', [
                'type' => 'warning',
                'message' => 'Payment is still processing. Please wait a moment and refresh.',
            ]);
        } catch (ShopOrderPaymentException $exception) {
            return redirect()->route('user.orders.index')
                ->with('toast', ['type' => 'error', 'message' => $exception->getMessage()]);
        } catch (ModelNotFoundException $exception) {
            throw $exception;
        } catch (\Throwable $exception) {
            Log::error('ShopOrder PayMongo verification failed', [
                'payment_public_id' => $paymentPublicId,
                'error' => $exception->getMessage(),
            ]);

            return redirect()->route('user.orders.index')->with('toast', [
                'type' => 'error',
                'message' => 'Could not verify payment. Please contact the shop.',
            ]);
        }
    }

    public function cancel(Request $request, string $paymentPublicId): RedirectResponse
    {
        $this->paymentService->cancelPending($request->user(), $paymentPublicId);

        return redirect()->route('user.orders.show', $paymentPublicId)->with('toast', [
            'type' => 'warning',
            'message' => 'Payment was cancelled. You can try again anytime.',
        ]);
    }

    public function check(Request $request, string $paymentPublicId): RedirectResponse
    {
        try {
            $result = $this->paymentService->checkForCustomer($request->user(), $paymentPublicId);
        } catch (ShopOrderPaymentException $exception) {
            return back()->with('toast', ['type' => 'error', 'message' => $exception->getMessage()]);
        } catch (ModelNotFoundException $exception) {
            throw $exception;
        } catch (\Throwable $exception) {
            Log::warning('Customer payment status check failed', [
                'payment_public_id' => $paymentPublicId,
                'error' => $exception->getMessage(),
            ]);

            return back()->with('toast', ['type' => 'error', 'message' => 'Could not check the payment right now. Please try again shortly.']);
        }

        return back()->with('toast', match ($result) {
            'confirmed' => ['type' => 'success', 'message' => 'Your payment was received. Thank you!'],
            'pending' => ['type' => 'warning', 'message' => 'The payment has not been completed yet. If you already paid, wait a minute and check again.'],
            default => ['type' => 'info', 'message' => 'There is no payment waiting to be confirmed.'],
        });
    }

    public function receipt(Request $request, string $paymentPublicId): Response|RedirectResponse
    {
        try {
            return Inertia::render('user/payment/Receipt', [
                'receipt' => $this->paymentService->receiptData($request->user(), $paymentPublicId),
            ]);
        } catch (ShopOrderPaymentException $exception) {
            return redirect()->route('user.orders.index')
                ->with('toast', ['type' => 'error', 'message' => $exception->getMessage()]);
        }
    }
}
