<?php

namespace App\Services;

use App\Models\ShopCustomer;
use App\Models\User;
use App\Repositories\ShopCustomerRepository;
use App\Support\CustomerIdentity;
use App\Support\PhilippinePhone;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Validation\ValidationException;

/**
 * A shop's customers: one record per person however many orders they place. An order is matched to
 * the customer with the same name, and a new customer is added the first time.
 */
class ShopCustomerService
{
    public function __construct(
        private readonly ShopCustomerRepository $customers,
        private readonly ShopContext $context,
    ) {}

    /**
     * The customer an order belongs to, created when this is their first order. Returns null for a
     * counter sale with no name and no number: it is not anyone's record.
     */
    public function resolveForOrder(
        int $shopId,
        ?string $name,
        ?string $phone,
        ?string $email = null,
        ?int $userId = null,
        ?string $customerPublicId = null,
    ): ?ShopCustomer {
        // Staff picked an existing customer from the search: that record is used as it is, whatever
        // name is typed. Only a customer of this shop can be picked.
        if (filled($customerPublicId)) {
            try {
                $customer = $this->customers->findByPublicIdForShop($customerPublicId, $shopId);
            } catch (ModelNotFoundException) {
                throw ValidationException::withMessages([
                    'shop_customer_id' => 'That customer could not be found. Search for them again.',
                ]);
            }

            return $this->refresh($customer, $phone, $email, $userId);
        }

        if (CustomerIdentity::isAnonymous($name, $phone)) {
            return null;
        }

        $name = trim((string) preg_replace('/\s+/u', ' ', (string) $name));
        $key = CustomerIdentity::key($name);
        $customer = $this->customers->findByIdentity($shopId, $key);

        if ($customer === null) {
            return $this->customers->create([
                'shop_id' => $shopId,
                'user_id' => $userId,
                'name' => $name,
                'phone' => PhilippinePhone::format($phone),
                'email' => filled($email) ? $email : null,
                'identity_key' => $key,
            ]);
        }

        return $this->refresh($customer, $phone, $email, $userId);
    }

    /**
     * The record follows the latest number the customer gave; the email and account are only filled
     * in when the shop does not have them yet. Each order keeps the number it was placed with.
     */
    private function refresh(ShopCustomer $customer, ?string $phone, ?string $email, ?int $userId): ShopCustomer
    {
        $formatted = PhilippinePhone::format($phone);
        $missing = array_filter([
            'phone' => filled($formatted) && $formatted !== $customer->phone ? $formatted : null,
            'email' => blank($customer->email) && filled($email) ? $email : null,
            'user_id' => $customer->user_id === null ? $userId : null,
        ], fn ($value) => $value !== null);

        return $missing === [] ? $customer : $this->customers->update($customer, $missing);
    }

    /** @return list<array{public_id: string, name: string, phone: string|null, email: string|null, orders: int}> */
    public function suggestions(User $actor, string $term): array
    {
        if (mb_strlen(trim($term)) < 2) {
            return [];
        }

        return $this->customers->search($this->context->shopIdFor($actor), trim($term))
            ->map(fn (ShopCustomer $customer) => [
                'public_id' => $customer->public_id,
                'name' => $customer->name,
                'phone' => $customer->phone,
                'email' => $customer->email,
                'orders' => (int) $customer->orders_count,
            ])
            ->all();
    }

    /** The customer list and the numbers shown above it. */
    public function listing(User $actor, array $filters): array
    {
        $shopId = $this->context->shopIdFor($actor);

        return [
            'customers' => $this->customers->paginateForShop($shopId, $filters, $filters['per_page']),
            'summary' => $this->customers->summaryForShop($shopId),
        ];
    }

    /** One customer with their numbers and orders. */
    public function profile(User $actor, string $publicId, int $perPage = 10): array
    {
        $customer = $this->customers->findByPublicIdForShop($publicId, $this->context->shopIdFor($actor));

        return [
            'customer' => $customer,
            'stats' => $this->customers->statsFor($customer),
            'orders' => $this->customers->ordersFor($customer, $perPage),
        ];
    }
}
