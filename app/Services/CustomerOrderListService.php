<?php

namespace App\Services;

use App\Http\Resources\CustomerOrderListResource;
use App\Models\User;
use App\Repositories\ShopOrderRepository;

class CustomerOrderListService
{
    public const PER_PAGE = 15;

    public function __construct(private readonly ShopOrderRepository $orders) {}

    /**
     * One page of the customer's orders with the filters applied, and how many orders they have
     * per tab (unfiltered, so the tabs always show the full picture).
     *
     * @param  array{status: ?string, payment: ?string, search: ?string}  $filters
     * @return array{orders: array<string, mixed>, counts: array<string, int>}
     */
    public function listFor(User $user, array $filters): array
    {
        $page = $this->orders->paginateForCustomer($user->id, $filters, self::PER_PAGE);
        $page->setCollection($page->getCollection()->map(
            fn ($order) => CustomerOrderListResource::make($order)->resolve(),
        ));

        return [
            'orders' => $page->toArray(),
            'counts' => $this->orders->statusCountsForCustomer($user->id),
        ];
    }
}
