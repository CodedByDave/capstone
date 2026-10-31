<?php

namespace App\Services;

use App\Models\BusinessAgreement;
use App\Models\BusinessAgreementAcceptance;
use App\Models\User;
use App\Repositories\BusinessAgreementRepository;
use App\Repositories\ShopRepository;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\UploadedFile;
use Illuminate\Validation\ValidationException;

class BusinessAgreementPdfService
{
    public function __construct(
        private readonly BusinessAgreementRepository $agreementRepository,
        private readonly ShopRepository $shopRepository,
    ) {}

    /**
     * Render the current agreement as a PDF for the checkout preview. When the
     * owner has chosen a signature image it is placed on the signature line.
     */
    public function previewPdf(User $user, array $data, ?UploadedFile $signature): string
    {
        $agreement = $this->agreementRepository->current();

        if (! $agreement) {
            throw ValidationException::withMessages([
                'agreement' => 'No active business agreement is currently available.',
            ]);
        }

        $shop = $this->shopRepository->findByOwnerId($user->id);
        $acceptance = $shop
            ? $this->agreementRepository->acceptanceFor($shop, $agreement)
            : null;

        if ($acceptance) {
            return $this->render($acceptance->agreement, $this->acceptanceView($acceptance));
        }

        return $this->render($agreement, [
            'businessName' => $data['business_name'] ?? $shop?->shop_name ?? 'The business identified in the application',
            'signerName' => $data['signer_name'] ?? $user->name,
            'signerRole' => $data['signer_role'] ?? 'Owner',
            'ownerSignature' => $signature
                ? $this->dataUri($signature->get(), $signature->getMimeType())
                : null,
        ]);
    }

    /**
     * @return array{contents: string, filename: string}
     */
    public function acceptancePdf(User $user, string $acceptancePublicId): array
    {
        $acceptance = $this->agreementRepository->findAcceptanceByPublicId($acceptancePublicId);

        if (! $acceptance) {
            throw ValidationException::withMessages([
                'agreement' => 'This agreement could not be found.',
            ]);
        }

        $canView = $user->isSuperAdmin()
            || ($user->isOwner() && $acceptance->shop?->owner_id === $user->id);

        if (! $canView) {
            throw new AuthorizationException('You cannot view this agreement.');
        }

        return [
            'contents' => $this->render($acceptance->agreement, $this->acceptanceView($acceptance)),
            'filename' => "business-agreement-v{$acceptance->agreement->version}.pdf",
        ];
    }

    private function acceptanceView(BusinessAgreementAcceptance $acceptance): array
    {
        $platform = $acceptance->platformSignature;

        return [
            'businessName' => $acceptance->business_name,
            'signerName' => $acceptance->signer_name,
            'signerRole' => $acceptance->signer_role,
            'ownerSignature' => $this->storedSignature($acceptance->signature_path),
            'signedAt' => $acceptance->accepted_at?->format('F j, Y'),
            'platformSignature' => $platform ? $this->storedSignature($platform->signature_path) : null,
            'platformSignerName' => $platform?->signer_name,
            'platformSignerRole' => $platform?->signer_role,
            'platformSignedAt' => $platform?->signed_at?->format('F j, Y'),
        ];
    }

    private function storedSignature(?string $path): ?string
    {
        if (! $path) {
            return null;
        }

        $file = $this->agreementRepository->signatureContents($path);

        return $this->dataUri($file['contents'], $file['mime_type']);
    }

    private function render(BusinessAgreement $agreement, array $view): string
    {
        return Pdf::loadView('pdf.business-agreement', array_merge([
            'signedAt' => null,
            'platformSignature' => null,
            'platformSignerName' => null,
            'platformSignerRole' => null,
            'platformSignedAt' => null,
        ], $view, [
            'title' => $agreement->title,
            'version' => $agreement->version,
            'effectiveAt' => $agreement->effective_at?->format('F j, Y') ?? 'Not specified',
            'clauses' => $this->clauses($agreement),
        ]))->setPaper('a4')->output();
    }

    private function clauses(BusinessAgreement $agreement): array
    {
        return collect(preg_split('/\R\s*\R/', $agreement->content))
            ->map(fn (string $paragraph) => trim($paragraph))
            ->filter(fn (string $paragraph) => $paragraph !== '' && $paragraph !== $agreement->title)
            ->map(function (string $paragraph) {
                if (preg_match('/^(\d+\.\s+[A-Z][A-Z &\/()-]+)\R([\s\S]+)$/', $paragraph, $match)) {
                    return ['heading' => $match[1], 'body' => $match[2]];
                }

                return ['heading' => null, 'body' => $paragraph];
            })
            ->values()
            ->all();
    }

    private function dataUri(string $contents, ?string $mimeType): string
    {
        return 'data:'.($mimeType ?: 'image/png').';base64,'.base64_encode($contents);
    }
}
