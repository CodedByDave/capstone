<?php

namespace App\Services;

use App\Models\Shop;
use App\Models\ShopOrder;
use App\Models\User;
use App\Repositories\CustomerAgreementRepository;
use App\Repositories\ShopRepository;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Validation\ValidationException;

class CustomerAgreementService
{
    public function __construct(
        private readonly CustomerAgreementRepository $repository,
        private readonly ShopRepository $shopRepository,
    ) {}

    public function agreementDataForShopId(int $shopId): array
    {
        $shop = $this->shopRepository->findShopById($shopId);
        if (! $shop) {
            throw (new ModelNotFoundException)->setModel(Shop::class, [$shopId]);
        }

        return $this->agreementData($shop);
    }

    public function agreementData(Shop $shop): array
    {
        $content = str_replace('{{shop_name}}', $shop->shop_name, (string) config('customer_service_agreement.content'));

        return [
            'title' => config('customer_service_agreement.title'),
            'version' => config('customer_service_agreement.version'),
            'effective_at' => config('customer_service_agreement.effective_at'),
            'content' => $content,
            'content_hash' => hash('sha256', $content),
        ];
    }

    public function record(
        ShopOrder $order,
        User $actor,
        string $submittedVersion,
        string $method,
        ?string $ipAddress,
        ?string $userAgent,
    ): void {
        $agreement = $this->agreementData($order->shop);

        if (! hash_equals((string) $agreement['version'], $submittedVersion)) {
            throw ValidationException::withMessages([
                'customer_agreement_accepted' => 'The customer agreement has changed. Please review and accept the current version.',
            ]);
        }

        $this->repository->createAcceptance([
            'shop_order_id' => $order->id,
            'shop_id' => $order->shop_id,
            'customer_user_id' => $order->user_id,
            'accepted_by_user_id' => $actor->id,
            'acceptance_method' => $method,
            'customer_name' => $order->customer_name,
            'agreement_version' => $agreement['version'],
            'agreement_title' => $agreement['title'],
            'agreement_content' => $agreement['content'],
            'content_hash' => $agreement['content_hash'],
            'accepted_at' => now(),
            'ip_address' => $ipAddress,
            'user_agent' => $userAgent,
        ]);
    }

    public function acceptanceData(ShopOrder $order): ?array
    {
        $acceptance = $this->repository->forOrder($order);

        return $acceptance ? [
            'title' => $acceptance->agreement_title,
            'version' => $acceptance->agreement_version,
            'content' => $acceptance->agreement_content,
            'accepted_at' => $acceptance->accepted_at?->toIso8601String(),
            'acceptance_method' => $acceptance->acceptance_method,
            'customer_name' => $acceptance->customer_name,
        ] : null;
    }
}
