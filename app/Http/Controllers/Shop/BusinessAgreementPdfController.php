<?php

namespace App\Http\Controllers\Shop;

use App\Http\Controllers\Controller;
use App\Http\Requests\Shop\PreviewBusinessAgreementRequest;
use App\Services\BusinessAgreementPdfService;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class BusinessAgreementPdfController extends Controller
{
    public function __construct(
        private readonly BusinessAgreementPdfService $pdfService,
    ) {}

    public function preview(PreviewBusinessAgreementRequest $request): Response
    {
        $pdf = $this->pdfService->previewPdf(
            $request->user(),
            $request->safe()->except('signature_image'),
            $request->file('signature_image'),
        );

        return response($pdf, 200, [
            'Content-Type' => 'application/pdf',
            'Cache-Control' => 'private, no-store',
            'Content-Disposition' => 'inline; filename="business-agreement-preview.pdf"',
        ]);
    }

    public function show(Request $request, string $acceptancePublicId): Response
    {
        $file = $this->pdfService->acceptancePdf($request->user(), $acceptancePublicId);

        return response($file['contents'], 200, [
            'Content-Type' => 'application/pdf',
            'Cache-Control' => 'private, no-store',
            'Content-Disposition' => 'inline; filename="'.$file['filename'].'"',
        ]);
    }
}
