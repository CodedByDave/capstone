<?php

namespace App\Repositories;

use App\Models\BusinessAgreement;
use App\Models\BusinessAgreementAcceptance;
use App\Models\BusinessAgreementPlatformSignature;
use App\Models\Order;
use App\Models\Shop;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

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
                'signature_method' => $data['signature_method'],
                'signature_path' => $data['signature_path'],
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

        return $acceptance->refresh()->load(['agreement', 'platformSignature']);
    }

    public function historyForShop(Shop $shop): Collection
    {
        return BusinessAgreementAcceptance::query()
            ->with(['agreement', 'platformSignature'])
            ->where('shop_id', $shop->id)
            ->latest('accepted_at')
            ->get();
    }

    public function acceptanceForOrder(Order $order): ?BusinessAgreementAcceptance
    {
        return BusinessAgreementAcceptance::query()
            ->with(['agreement', 'platformSignature'])
            ->where('order_id', $order->id)
            ->first();
    }

    public function findAcceptanceByPublicId(string $publicId): ?BusinessAgreementAcceptance
    {
        return BusinessAgreementAcceptance::query()
            ->with(['agreement', 'shop', 'platformSignature'])
            ->where('public_id', $publicId)
            ->first();
    }

    public function storeSignature(
        UploadedFile $signature,
        Shop $shop,
        BusinessAgreement $agreement,
    ): string {
        return $signature->store(
            "agreements/{$shop->id}/{$agreement->public_id}",
            'private',
        );
    }

    public function createPlatformSignature(
        BusinessAgreementAcceptance $acceptance,
        User $admin,
        array $data,
    ): BusinessAgreementPlatformSignature {
        return BusinessAgreementPlatformSignature::query()->create([
            'business_agreement_acceptance_id' => $acceptance->id,
            'admin_user_id' => $admin->id,
            'signer_name' => $data['signer_name'],
            'signer_role' => $data['signer_role'],
            'signature_method' => $data['signature_method'],
            'signature_path' => $data['signature_path'],
            'signed_at' => now(),
            'ip_address' => $data['ip_address'],
            'user_agent' => $data['user_agent'],
        ]);
    }

    public function storePlatformSignature(
        UploadedFile $signature,
        BusinessAgreementAcceptance $acceptance,
    ): string {
        return $signature->store(
            "agreements/{$acceptance->shop_id}/{$acceptance->agreement->public_id}/platform",
            'private',
        );
    }

    public function findPlatformSignatureByPublicId(string $publicId): ?BusinessAgreementPlatformSignature
    {
        return BusinessAgreementPlatformSignature::query()
            ->with('acceptance.shop')
            ->where('public_id', $publicId)
            ->first();
    }

    public function refreshAcceptance(BusinessAgreementAcceptance $acceptance): BusinessAgreementAcceptance
    {
        return $acceptance->refresh()->load(['agreement', 'platformSignature']);
    }

    public function signatureContents(string $path): array
    {
        if (! Storage::disk('private')->exists($path)) {
            throw (new ModelNotFoundException)->setModel(BusinessAgreementAcceptance::class);
        }

        return [
            'contents' => Storage::disk('private')->get($path),
            'mime_type' => Storage::disk('private')->mimeType($path) ?: 'image/png',
        ];
    }
}
