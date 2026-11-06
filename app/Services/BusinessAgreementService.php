<?php

namespace App\Services;

use App\Models\BusinessAgreement;
use App\Models\BusinessAgreementAcceptance;
use App\Models\Order;
use App\Models\Shop;
use App\Models\User;
use App\Repositories\BusinessAgreementRepository;
use App\Repositories\ShopRepository;
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

    /**
     * A shop accepts the agreement once, during its free trial. When the trial
     * owner applies for a paid plan, that acceptance moves to the new order so
     * the admin sees it with the plan. Acceptances countersigned under the old
     * signing process stay with the order they were signed for.
     */
    public function carryAcceptanceToOrder(User $user, Order $order): void
    {
        $shop = $this->shopRepository->findByOwnerId($user->id);
        $agreement = $this->agreementRepository->current();

        if (! $shop || ! $agreement) {
            return;
        }

        $acceptance = $this->agreementRepository->acceptanceFor($shop, $agreement);

        if ($acceptance && ! $acceptance->platformSignature) {
            $this->agreementRepository->reassignOrder($acceptance, $order);
        }
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

        // Acceptance is recorded by the typed name, role and time; no signature image is collected.
        $acceptance = $this->agreementRepository->createAcceptance(
            $agreement,
            $shop,
            $user,
            $order,
            [
                'signer_name' => $data['signer_name'],
                'signer_role' => $data['signer_role'],
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
        return [
            'public_id' => $acceptance->public_id,
            'business_name' => $acceptance->business_name,
            'signer_name' => $acceptance->signer_name,
            'signer_role' => $acceptance->signer_role,
            'accepted_at' => $acceptance->accepted_at?->toIso8601String(),
            'content_hash' => $acceptance->content_hash,
            'agreement' => [
                'title' => $acceptance->agreement->title,
                'version' => $acceptance->agreement->version,
                'content' => $acceptance->agreement->content,
                'effective_at' => $acceptance->agreement->effective_at?->toIso8601String(),
            ],
        ];
    }
}
