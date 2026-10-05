<?php

namespace App\Http\Controllers\Shop;

use App\Http\Controllers\Controller;
use App\Http\Requests\Shop\AcceptBusinessAgreementRequest;
use App\Services\BusinessAgreementService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class BusinessAgreementController extends Controller
{
    public function __construct(
        private readonly BusinessAgreementService $agreementService,
    ) {}

    public function show(Request $request): Response
    {
        return Inertia::render(
            'shop/LegalDocuments',
            $this->agreementService->legalDocumentsData($request->user()),
        );
    }

    public function accept(AcceptBusinessAgreementRequest $request): RedirectResponse
    {
        $this->agreementService->acceptStandalone(
            $request->user(),
            $request->validated(),
            $request->ip(),
            $request->userAgent(),
        );

        return redirect()->intended(route('shop.dashboard'))->with('toast', [
            'type' => 'success',
            'message' => 'Business agreement accepted successfully.',
        ]);
    }
}
