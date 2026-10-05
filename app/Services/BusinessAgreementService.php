<?php

namespace App\Services;

use App\Models\BusinessAgreement;
use App\Models\BusinessAgreementAcceptance;
use App\Models\BusinessAgreementPlatformSignature;
use App\Models\Order;
use App\Models\Shop;
use App\Models\User;
use App\Repositories\BusinessAgreementRepository;
use App\Repositories\ShopRepository;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class BusinessAgreementService
{
    public function __construct(
        private readonly BusinessAgreementRepository $agreementRepository,
        private readonly ShopRepository $shopRepository,
    ) {}

    public function currentAgreementData(User $user): array
    {
        $agreement = $this->currentAgreement();
        $shop = $this->shopRepository->findByOwnerId($user->id);
        $acceptance = $shop
            ? $this->agreementRepository->acceptanceFor($shop, $agreement)
            : null;

        return $this->serializeAgreement($agreement, $acceptance);
    }

    public function legalDocumentsData(User $user): array
    {
        $shop = $this->shopRepository->findByOwnerId($user->id);

        if (! $shop) {
            throw ValidationException::withMessages([
                'agreement' => 'No business is linked to this account.',
            ]);
        }

        return [
            'currentAgreement' => $this->currentAgreementData($user),
            'history' => $this->agreementRepository->historyForShop($shop)
                ->map(fn (BusinessAgreementAcceptance $acceptance) => $this->serializeAcceptance($acceptance))
                ->values()
                ->all(),
        ];
    }

    public function acceptForOrder(
        User $user,
        Order $order,
        array $data,
        ?string $ipAddress,
        ?string $userAgent,
    ): BusinessAgreementAcceptance {
        $shop = $this->shopRepository->findByOwnerId($user->id);

        if (! $shop) {
            throw ValidationException::withMessages([
                'agreement' => 'A business profile is required before accepting the agreement.',
            ]);
        }

        return $this->recordAcceptance(
            $user,
            $shop,
            $order,
            $data,
            $ipAddress,
            $userAgent,
        );
    }

    public function acceptStandalone(
        User $user,
        array $data,
        ?string $ipAddress,
        ?string $userAgent,
    ): BusinessAgreementAcceptance {
        return DB::transaction(function () use ($user, $data, $ipAddress, $userAgent) {
            $shop = $this->shopRepository->findByOwnerId($user->id);

            if (! $shop) {
                throw ValidationException::withMessages([
                    'agreement' => 'A business profile is required before accepting the agreement.',
                ]);
            }

            return $this->recordAcceptance(
                $user,
                $shop,
                null,
                $data,
                $ipAddress,
                $userAgent,
            );
        });
    }

    public function requiresCurrentAcceptance(User $user): bool
    {
        if (! $user->isOwner()) {
            return false;
        }

        $shop = $this->shopRepository->findByOwnerId($user->id);

        if (! $shop || ! in_array($shop->status, ['active', 'inactive'], true)) {
            return false;
        }

        $agreement = $this->agreementRepository->current();

        return $agreement !== null
            && $this->agreementRepository->acceptanceFor($shop, $agreement) === null;
    }

    public function acceptanceDataForOrder(Order $order): ?array
    {
        $acceptance = $this->agreementRepository->acceptanceForOrder($order);

        return $acceptance ? $this->serializeAcceptance($acceptance) : null;
    }

    public function currentAgreementDataForShop(Shop $shop): array
    {
        $agreement = $this->currentAgreement();
        $acceptance = $this->agreementRepository->acceptanceFor($shop, $agreement);

        return $this->serializeAgreement($agreement, $acceptance);
    }

    public function signatureFor(User $user, string $acceptancePublicId): array
    {
        $acceptance = $this->agreementRepository->findAcceptanceByPublicId($acceptancePublicId);

        if (! $acceptance || ! $acceptance->signature_path) {
            throw ValidationException::withMessages([
                'signature' => 'No signature image is available for this acceptance.',
            ]);
        }

        $canView = $user->isSuperAdmin()
            || ($user->isOwner() && $acceptance->shop?->owner_id === $user->id);

        if (! $canView) {
            throw new AuthorizationException('You cannot view this agreement signature.');
        }

        return $this->agreementRepository->signatureContents($acceptance->signature_path);
    }

    public function platformSignatureFor(User $user, string $signaturePublicId): array
    {
        $signature = $this->agreementRepository->findPlatformSignatureByPublicId($signaturePublicId);

        if (! $signature) {
            throw ValidationException::withMessages([
                'signature' => 'No platform signature is available for this agreement.',
            ]);
        }

        $canView = $user->isSuperAdmin()
            || ($user->isOwner() && $signature->acceptance->shop?->owner_id === $user->id);

        if (! $canView) {
            throw new AuthorizationException('You cannot view this platform signature.');
        }

        return $this->agreementRepository->signatureContents($signature->signature_path);
    }

    public function countersignForOrder(
        User $admin,
        Order $order,
        array $data,
        ?string $ipAddress,
        ?string $userAgent,
    ): BusinessAgreementAcceptance {
        if (! $admin->isSuperAdmin()) {
            throw new AuthorizationException('Only an authorized platform administrator may countersign agreements.');
        }

        $acceptance = $this->agreementRepository->acceptanceForOrder($order);

        if (! $acceptance || ! $acceptance->signature_path) {
            throw ValidationException::withMessages([
                'agreement' => 'The shop must sign the current agreement before this order can be approved.',
            ]);
        }

        if ($acceptance->platformSignature) {
            throw ValidationException::withMessages([
                'agreement' => 'This agreement has already been countersigned by LaundryHub.',
            ]);
        }

        $signaturePath = $this->agreementRepository->storePlatformSignature(
            $data['platform_signature_image'],
            $acceptance,
        );

        $this->agreementRepository->createPlatformSignature($acceptance, $admin, [
            'signer_name' => $data['platform_signer_name'],
            'signer_role' => $data['platform_signer_role'],
            'signature_method' => $data['platform_signature_method'],
            'signature_path' => $signaturePath,
            'ip_address' => $ipAddress,
            'user_agent' => $userAgent ? mb_substr($userAgent, 0, 2000) : null,
        ]);

        return $this->agreementRepository->refreshAcceptance($acceptance);
    }

    private function recordAcceptance(
        User $user,
        Shop $shop,
        ?Order $order,
        array $data,
        ?string $ipAddress,
        ?string $userAgent,
    ): BusinessAgreementAcceptance {
        $agreement = $this->agreementRepository->findCurrentByPublicId(
            $data['agreement_public_id'],
        );

        if (! $agreement) {
            throw ValidationException::withMessages([
                'agreement_public_id' => 'The agreement has changed. Please review the current version.',
            ]);
        }

        $existingAcceptance = $this->agreementRepository->acceptanceFor($shop, $agreement);

        if ($existingAcceptance) {
            if ($order && $existingAcceptance->order_id === null) {
                return $this->agreementRepository->attachOrderWhenMissing($existingAcceptance, $order);
            }

            return $existingAcceptance;
        }

        if (empty($data['signature_image']) || empty($data['signature_method'])) {
            throw ValidationException::withMessages([
                'signature_image' => 'Draw the owner signature or upload a signature image to continue.',
            ]);
        }

        $signaturePath = $this->agreementRepository->storeSignature(
            $data['signature_image'],
            $shop,
            $agreement,
        );

        $acceptance = $this->agreementRepository->createAcceptance(
            $agreement,
            $shop,
            $user,
            $order,
            [
                'signer_name' => $data['signer_name'],
                'signer_role' => $data['signer_role'],
                'signature_method' => $data['signature_method'],
                'signature_path' => $signaturePath,
                'ip_address' => $ipAddress,
                'user_agent' => $userAgent ? mb_substr($userAgent, 0, 2000) : null,
            ],
        );

        if ($order && $acceptance->order_id === null) {
            return $this->agreementRepository->attachOrderWhenMissing($acceptance, $order);
        }

        return $acceptance->loadMissing('agreement');
    }

    private function currentAgreement(): BusinessAgreement
    {
        $agreement = $this->agreementRepository->current();

        if (! $agreement) {
            throw ValidationException::withMessages([
                'agreement' => 'No active business agreement is currently available.',
            ]);
        }

        return $agreement;
    }

    private function serializeAgreement(
        BusinessAgreement $agreement,
        ?BusinessAgreementAcceptance $acceptance,
    ): array {
        return [
            'public_id' => $agreement->public_id,
            'title' => $agreement->title,
            'version' => $agreement->version,
            'content' => $agreement->content,
            'content_hash' => $agreement->content_hash,
            'effective_at' => $agreement->effective_at?->toIso8601String(),
            'accepted' => $acceptance !== null,
            'acceptance' => $acceptance ? $this->serializeAcceptance($acceptance) : null,
        ];
    }

    private function serializeAcceptance(BusinessAgreementAcceptance $acceptance): array
    {
        $platformSignature = $acceptance->platformSignature;

        return [
            'public_id' => $acceptance->public_id,
            'business_name' => $acceptance->business_name,
            'signer_name' => $acceptance->signer_name,
            'signer_role' => $acceptance->signer_role,
            'accepted_at' => $acceptance->accepted_at?->toIso8601String(),
            'content_hash' => $acceptance->content_hash,
            'signature_method' => $acceptance->signature_method,
            'signature_url' => $acceptance->signature_path
                ? route('business-agreement.signature', $acceptance->public_id)
                : null,
            'execution_status' => $platformSignature
                ? 'fully_executed'
                : 'awaiting_platform_signature',
            'platform_signature' => $platformSignature
                ? $this->serializePlatformSignature($platformSignature)
                : null,
            'agreement' => [
                'title' => $acceptance->agreement->title,
                'version' => $acceptance->agreement->version,
                'content' => $acceptance->agreement->content,
                'effective_at' => $acceptance->agreement->effective_at?->toIso8601String(),
            ],
        ];
    }

    private function serializePlatformSignature(BusinessAgreementPlatformSignature $signature): array
    {
        return [
            'public_id' => $signature->public_id,
            'signer_name' => $signature->signer_name,
            'signer_role' => $signature->signer_role,
            'signed_at' => $signature->signed_at?->toIso8601String(),
            'signature_method' => $signature->signature_method,
            'signature_url' => route('business-agreement.platform-signature', $signature->public_id),
        ];
    }
}
