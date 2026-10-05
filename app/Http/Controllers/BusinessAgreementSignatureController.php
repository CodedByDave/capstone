<?php

namespace App\Http\Controllers;

use App\Services\BusinessAgreementService;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class BusinessAgreementSignatureController extends Controller
{
    public function __construct(
        private readonly BusinessAgreementService $agreementService,
    ) {}

    public function show(Request $request, string $acceptancePublicId): Response
    {
        $signature = $this->agreementService->signatureFor(
            $request->user(),
            $acceptancePublicId,
        );

        return response($signature['contents'], 200, [
            'Content-Type' => $signature['mime_type'],
            'Cache-Control' => 'private, no-store',
            'Content-Disposition' => 'inline; filename="owner-signature"',
        ]);
    }

    public function platformShow(Request $request, string $signaturePublicId): Response
    {
        $signature = $this->agreementService->platformSignatureFor(
            $request->user(),
            $signaturePublicId,
        );

        return response($signature['contents'], 200, [
            'Content-Type' => $signature['mime_type'],
            'Cache-Control' => 'private, no-store',
            'Content-Disposition' => 'inline; filename="platform-signature"',
        ]);
    }
}
