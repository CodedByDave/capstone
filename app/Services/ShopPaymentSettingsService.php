<?php

namespace App\Services;

use App\Models\Shop;
use App\Models\User;
use App\Repositories\ShopRepository;
use Illuminate\Validation\ValidationException;

class ShopPaymentSettingsService
{
    public function __construct(
        private readonly ShopRepository $shopRepository,
        private readonly PaymongoService $paymongoService,
    ) {}

    public function pageData(User $owner): array
    {
        $shop = $this->shopForOwner($owner);

        return [
            'paymongo_configured' => $shop->hasPaymongo(),
            'paymongo_public_key' => $shop->paymongo_public_key,
            'payment_mode' => $this->usesDemoCredentials($shop) ? 'demo' : ($shop->hasPaymongo() ? 'paymongo' : null),
            'demo_enabled' => $this->demoEnabled(),
            'demo_credentials' => $this->demoEnabled() ? [
                'secret_key' => (string) config('services.paymongo.demo_secret_key'),
                'public_key' => (string) config('services.paymongo.demo_public_key'),
            ] : null,
        ];
    }

    public function update(User $owner, array $data): array
    {
        $secretKey = $data['paymongo_secret_key'] ?? null;
        $publicKey = $data['paymongo_public_key'] ?? null;
        $shop = $this->shopForOwner($owner);

        if ($secretKey === null && $publicKey === null) {
            $this->shopRepository->updatePaymongoCredentials($shop, null, null);

            return ['type' => 'success', 'message' => 'Online payments disconnected.'];
        }

        if ($this->matchesDemoPair($secretKey, $publicKey)) {
            $this->shopRepository->updatePaymongoCredentials($shop, $secretKey, $publicKey);

            return ['type' => 'success', 'message' => 'Demo gateway connected. Customers will use PayMongo test checkout; no real money will be charged.'];
        }

        if (! $this->paymongoService->verifySecretKey((string) $secretKey)) {
            throw ValidationException::withMessages([
                'paymongo_secret_key' => 'Invalid secret key. Use the displayed demo pair or credentials from PayMongo.',
            ]);
        }

        $this->shopRepository->updatePaymongoCredentials($shop, $secretKey, $publicKey);

        return ['type' => 'success', 'message' => 'PayMongo connected successfully.'];
    }

    public function usesDemoCredentials(Shop $shop): bool
    {
        return $this->matchesDemoPair($shop->paymongo_secret_key, $shop->paymongo_public_key);
    }

    private function matchesDemoPair(?string $secretKey, ?string $publicKey): bool
    {
        return $this->demoEnabled()
            && hash_equals((string) config('services.paymongo.demo_secret_key'), (string) $secretKey)
            && hash_equals((string) config('services.paymongo.demo_public_key'), (string) $publicKey);
    }

    private function demoEnabled(): bool
    {
        return (bool) config('services.paymongo.demo_enabled');
    }

    private function shopForOwner(User $owner): Shop
    {
        return $this->shopRepository->findByOwnerId($owner->id)
            ?? throw ValidationException::withMessages(['shop' => 'No shop is registered for this account.']);
    }
}
