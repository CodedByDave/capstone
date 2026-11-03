<?php

namespace App\Http\Controllers\Shop;

use App\Exceptions\OrderSubmissionException;
use App\Http\Controllers\Controller;
use App\Http\Requests\Shop\StoreKycVerificationRequest;
use App\Services\KycVerificationService;
use Illuminate\Http\RedirectResponse;

class KycVerificationController extends Controller
{
    public function __construct(private readonly KycVerificationService $verifications) {}

    public function store(StoreKycVerificationRequest $request): RedirectResponse
    {
        try {
            $this->verifications->submit(
                $request->user(),
                $request->validated(),
                $request->ip(),
                $request->userAgent(),
            );
        } catch (OrderSubmissionException $exception) {
            return back()->with('toast', ['type' => 'error', 'message' => $exception->getMessage()]);
        }

        return redirect()->route('shop.dashboard')->with('toast', [
            'type' => 'success',
            'message' => 'Verification submitted. Our admin will review your documents.',
        ]);
    }
}
