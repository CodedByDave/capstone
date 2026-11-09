<?php

namespace App\Repositories;

use App\Models\Delivery;
use App\Models\DeliveryPhoto;
use Illuminate\Database\Eloquent\Collection;

class DeliveryPhotoRepository extends Repository
{
    public function __construct(DeliveryPhoto $photo)
    {
        parent::__construct($photo);
    }

    public function countForDelivery(Delivery $delivery): int
    {
        return DeliveryPhoto::query()->where('delivery_id', $delivery->id)->count();
    }

    /** @return Collection<int, DeliveryPhoto> Photos on the order's pickup run, oldest first. */
    public function forPickupRunOfOrder(int $shopOrderId): Collection
    {
        return DeliveryPhoto::query()
            ->whereHas('delivery', fn ($query) => $query
                ->where('shop_order_id', $shopOrderId)
                ->where('type', Delivery::TYPE_PICKUP))
            ->orderBy('id')
            ->get();
    }

    public function createForDelivery(Delivery $delivery, int $uploadedBy, string $path, string $kind = DeliveryPhoto::KIND_PROOF): DeliveryPhoto
    {
        return DeliveryPhoto::query()->create([
            'delivery_id' => $delivery->id,
            'uploaded_by' => $uploadedBy,
            'path' => $path,
            'kind' => $kind,
        ]);
    }
}
