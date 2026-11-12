<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ApproveKycVerificationRequest;
use App\Http\Requests\Admin\KycVerificationFilterRequest;
use App\Http\Requests\Admin\RejectKycVerificationRequest;
use App\Http\Resources\KycVerificationResource;
use App\Models\KycVerification;
use App\Services\KycReviewService;
use App\Services\KycVerificationService;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class KycVerificationController extends Controller
{
    public function __construct(
        private readonly KycVerificationService $verifications,
        private readonly KycReviewService $review,
    ) {}

    public function index(KycVerificationFilterRequest $request): Response
    {
        $filters = $request->filters();

        return Inertia::render('admin/kyc/Index', [
            'verifications' => $this->verifications->paginateForAdmin($filters)
                ->through(fn (KycVerification $v) => KycVerificationResource::make($v)->resolve()),
            'counts' => $this->verifications->statusCounts(),
            'filters' => $filters,
        ]);
    }

    public function show(KycVerification $verification): Response
    {
        $verification = $this->verifications->loadForAdmin($verification);

        return Inertia::render('admin/kyc/Show', [
            'verification' => KycVerificationResource::make($verification)->resolve(),
            'kycReview' => $this->review->rows($verification),
            'approvalBlockers' => $this->review->approvalBlockers($verification),
            'kycFlagOptions' => $this->review->flagOptions(),
        ]);
    }

    public function approve(ApproveKycVerificationRequest $request, KycVerification $verification): RedirectResponse
    {
        $approved = $this->verifications->approve($request->user(), $verification);

        return back()->with('toast', [
            'type' => 'success',
            'message' => "{$approved->shop_name} is verified. Their free trial activates automatically.",
        ]);
    }

    public function reject(RejectKycVerificationRequest $request, KycVerification $verification): RedirectResponse
    {
        $rejected = $this->verifications->reject(
            $request->user(),
            $verification,
            $request->validated('rejection_reason'),
            $request->validated('flags') ?? [],
        );

        return back()->with('toast', [
            'type' => 'error',
            'message' => "{$rejected->shop_name} verification rejected. The owner has been notified.",
        ]);
    }
}
