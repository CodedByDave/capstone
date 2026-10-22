<?php

namespace App\Repositories;

use App\Models\Delivery;
use App\Models\DeliveryPhoto;

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

    public function createForDelivery(Delivery $delivery, int $uploadedBy, string $path): DeliveryPhoto
    {
        return DeliveryPhoto::query()->create([
            'delivery_id' => $delivery->id,
            'uploaded_by' => $uploadedBy,
            'path' => $path,
        ]);
    }
}
