<?php

namespace App\Repositories;

use App\Models\BusinessAgreement;
use App\Models\BusinessAgreementAcceptance;
use App\Models\Order;
use App\Models\Shop;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;

class BusinessAgreementRepository
{
    public function current(): ?BusinessAgreement
    {
        return BusinessAgreement::query()
            ->where('is_active', true)
            ->whereNotNull('published_at')
            ->where('effective_at', '<=', now())
            ->latest('effective_at')
            ->first();
    }

    public function findCurrentByPublicId(string $publicId): ?BusinessAgreement
    {
        return BusinessAgreement::query()
            ->where('public_id', $publicId)
            ->where('is_active', true)
            ->whereNotNull('published_at')
            ->where('effective_at', '<=', now())
            ->first();
    }

    public function acceptanceFor(Shop $shop, BusinessAgreement $agreement): ?BusinessAgreementAcceptance
    {
        return BusinessAgreementAcceptance::query()
            ->with(['agreement', 'platformSignature'])
            ->where('shop_id', $shop->id)
            ->where('business_agreement_id', $agreement->id)
            ->first();
    }

    public function createAcceptance(
        BusinessAgreement $agreement,
        Shop $shop,
        User $user,
        ?Order $order,
        array $data,
    ): BusinessAgreementAcceptance {
        return BusinessAgreementAcceptance::query()->firstOrCreate(
            [
                'business_agreement_id' => $agreement->id,
                'shop_id' => $shop->id,
            ],
            [
                'user_id' => $user->id,
                'order_id' => $order?->id,
                'business_name' => $shop->shop_name,
                'signer_name' => $data['signer_name'],
                'signer_role' => $data['signer_role'],
                // Signatures are no longer collected; the columns stay for agreements signed earlier.
                'signature_method' => null,
                'signature_path' => null,
                'accepted_at' => now(),
                'ip_address' => $data['ip_address'],
                'user_agent' => $data['user_agent'],
                'content_hash' => $agreement->content_hash,
            ],
        );
    }

    public function attachOrderWhenMissing(
        BusinessAgreementAcceptance $acceptance,
        Order $order,
    ): BusinessAgreementAcceptance {
        if ($acceptance->order_id === null) {
            $acceptance->update(['order_id' => $order->id]);
        }

        return $acceptance->refresh()->load('agreement');
    }

    public function reassignOrder(
        BusinessAgreementAcceptance $acceptance,
        Order $order,
    ): BusinessAgreementAcceptance {
        $acceptance->update(['order_id' => $order->id]);

        return $acceptance->refresh()->load('agreement');
    }

    public function historyForShop(Shop $shop): Collection
    {
        return BusinessAgreementAcceptance::query()
            ->with('agreement')
            ->where('shop_id', $shop->id)
            ->latest('accepted_at')
            ->get();
    }

    public function acceptanceForOrder(Order $order): ?BusinessAgreementAcceptance
    {
        return BusinessAgreementAcceptance::query()
            ->with('agreement')
            ->where('order_id', $order->id)
            ->first();
    }

    public function findAcceptanceByPublicId(string $publicId): ?BusinessAgreementAcceptance
    {
        return BusinessAgreementAcceptance::query()
            ->with(['agreement', 'shop'])
            ->where('public_id', $publicId)
            ->first();
    }
}
