<?php

namespace App\Http\Controllers\Shop;

use App\Exceptions\OrderSubmissionException;
use App\Http\Controllers\Controller;
use App\Http\Requests\Shop\StartTrialRequest;
use App\Services\TrialService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

class TrialController extends Controller
{
    public function __construct(
        private readonly TrialService $trialService,
    ) {}

    public function show(Request $request): Response|RedirectResponse
    {
        $availability = $this->trialService->availability($request->user());

        if ($availability['has_open_application']) {
            return redirect()->route('shop.dashboard')->with('toast', [
                'type' => 'info',
                'message' => 'You already have an active plan.',
            ]);
        }

        if ($availability['has_used_trial']) {
            return redirect()->route('plans')->with('toast', [
                'type' => 'info',
                'message' => 'You have already used your free trial. Choose a plan to continue.',
            ]);
        }

        return Inertia::render('shop/TrialStart', $this->trialService->pageData($request->user()));
    }

    public function start(StartTrialRequest $request): RedirectResponse
    {
        try {
            $this->trialService->start(
                $request->user(),
                $request->validated(),
                $request->ip(),
                $request->userAgent(),
            );

            return redirect()->route('shop.dashboard')->with('toast', [
                'type' => 'success',
                'message' => 'Your '.TrialService::TRIAL_DAYS.'-day free trial has started! Explore the platform.',
            ]);
        } catch (OrderSubmissionException $exception) {
            return back()->with('toast', [
                'type' => 'error',
                'message' => $exception->getMessage(),
            ]);
        } catch (ValidationException $exception) {
            throw $exception;
        } catch (\Throwable $exception) {
            Log::error('Trial start failed', [
                'error' => $exception->getMessage(),
                'user_id' => $request->user()->id,
            ]);

            return back()->with('toast', [
                'type' => 'error',
                'message' => 'Something went wrong. Please try again.',
            ]);
        }
    }
}
