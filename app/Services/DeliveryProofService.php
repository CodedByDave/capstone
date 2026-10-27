<?php

namespace App\Services;

use App\Models\Delivery;
use App\Models\DeliveryPhoto;
use App\Models\User;
use App\Repositories\DeliveryPhotoRepository;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

/**
 * Photos the shop attaches to a delivery as proof. They are kept on the private disk and
 * only the owning shop and the customer of the linked order can open them.
 */
class DeliveryProofService
{
    public const MAX_PHOTOS = 8;

    private const DISK = 'private';

    public function __construct(
        private readonly DeliveryPhotoRepository $photos,
        private readonly LogisticsService $logistics,
    ) {}

    /**
     * @param  list<UploadedFile>  $files
     * @param  string  $kind  DeliveryPhoto::KIND_PROOF for the delivery itself, KIND_CASH for the cash collected
     */
    public function add(User $user, Delivery $delivery, array $files, string $kind = DeliveryPhoto::KIND_PROOF): void
    {
        $this->assertDeliveryInShop($user, $delivery);

        if ($this->photos->countForDelivery($delivery) + count($files) > self::MAX_PHOTOS) {
            throw ValidationException::withMessages([
                'photos' => 'A delivery can have at most '.self::MAX_PHOTOS.' proof photos.',
            ]);
        }

        $directory = "delivery-proofs/{$delivery->shop_id}/{$delivery->public_id}";

        foreach ($files as $file) {
            $path = $file->store($directory, self::DISK);
            $this->photos->createForDelivery($delivery, $user->id, $path, $kind);
        }
    }

    public function remove(User $user, DeliveryPhoto $photo): void
    {
        $this->assertPhotoInShop($user, $photo);

        $path = $photo->path;
        $this->photos->delete($photo);
        Storage::disk(self::DISK)->delete($path);
    }

    /** The file path a shop user may open, or a not-found error. */
    public function pathForShop(User $user, DeliveryPhoto $photo): string
    {
        $this->assertPhotoInShop($user, $photo);

        return $this->existingPath($photo);
    }

    /** The file path the customer of the linked order may open, or a not-found error. */
    public function pathForCustomer(User $customer, DeliveryPhoto $photo): string
    {
        $order = $photo->delivery?->order;

        if ($order === null || $order->user_id !== $customer->id) {
            throw (new ModelNotFoundException)->setModel(DeliveryPhoto::class);
        }

        return $this->existingPath($photo);
    }

    private function existingPath(DeliveryPhoto $photo): string
    {
        if (! Storage::disk(self::DISK)->exists($photo->path)) {
            throw (new ModelNotFoundException)->setModel(DeliveryPhoto::class);
        }

        return $photo->path;
    }

    // A record from another shop is reported exactly like a missing one.

    private function assertDeliveryInShop(User $user, Delivery $delivery): void
    {
        if ($delivery->shop_id !== $this->logistics->shopIdFor($user)) {
            throw (new ModelNotFoundException)->setModel(Delivery::class);
        }
    }

    private function assertPhotoInShop(User $user, DeliveryPhoto $photo): void
    {
        $photo->loadMissing('delivery');
        $this->assertDeliveryInShop($user, $photo->delivery);
    }
}
