<?php

namespace App\Services;

use App\Models\ShopService;
use App\Repositories\ShopServiceRepository;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class ShopServiceService
{
    public function __construct(
        protected ShopServiceRepository $repo,
        protected InventoryService $inventory,
    ) {}

    public function getAll(int $shopId, bool $activeOnly = false, bool $onlyTrashed = false): Collection
    {
        return $this->repo->allByShop($shopId, $activeOnly, $onlyTrashed);
    }

    /**
     * A service of another shop is reported exactly like one that does not exist, so a service id
     * cannot be used to probe or change another shop's catalogue.
     *
     * @throws ModelNotFoundException
     */
    public function assertInShop(ShopService $service, int $shopId): ShopService
    {
        if ((int) $service->shop_id !== $shopId) {
            throw (new ModelNotFoundException)->setModel(ShopService::class, [$service->id]);
        }

        return $service;
    }

    /**
     * Items of the shop's inventory a service can be set to use.
     *
     * @return list<array{id: int, name: string, unit: string, quantity: int}>
     */
    public function supplyOptions(int $shopId): array
    {
        return $this->inventory->all($shopId)
            ->where('status', 'active')
            ->map(fn ($item) => ['id' => $item->id, 'name' => $item->name, 'unit' => $item->unit, 'quantity' => $item->quantity])
            ->values()
            ->all();
    }

    /** What the edit form needs to show the supplies a service already uses. */
    public function supplyLines(ShopService $service): array
    {
        return $service->supplies()->get()->map(fn ($item) => [
            'inventory_id' => $item->id,
            'quantity' => (float) $item->pivot->quantity,
            'unit' => $item->pivot->unit,
        ])->all();
    }

    /** @param  list<UploadedFile>  $photos  up to three, the first becomes the cover */
    public function create(array $data, array $photos = []): ShopService
    {
        $this->validatePricingFields($data);
        $this->assertPhotoCount(count($photos));
        [$data, $supplies] = $this->splitSupplies($data, (int) $data['shop_id']);

        $paths = $this->storePhotos($photos, (int) $data['shop_id']);

        try {
            return $this->createWithSupplies([...$data, ...$this->photoColumns($paths)], $supplies);
        } catch (\Throwable $exception) {
            Storage::disk('public')->delete($paths);

            throw $exception;
        }
    }

    /**
     * @param  list<UploadedFile>  $photos  new photos, added after the saved ones that are kept
     * @param  list<int>  $removePhotos  positions of saved photos to take off (0 = cover)
     */
    public function update(ShopService $service, array $data, array $photos = [], array $removePhotos = []): ShopService
    {
        $this->validatePricingFields($data);
        [$data, $supplies] = $this->splitSupplies($data, (int) $service->shop_id);

        $current = $service->photoPaths();
        $removed = array_values(array_intersect_key($current, array_flip($removePhotos)));
        $kept = array_values(array_diff_key($current, array_flip($removePhotos)));
        $this->assertPhotoCount(count($kept) + count($photos));

        $newPaths = $this->storePhotos($photos, (int) $service->shop_id);
        if ($newPaths !== [] || $removed !== []) {
            $data = [...$data, ...$this->photoColumns([...$kept, ...$newPaths])];
        }

        try {
            $updated = DB::transaction(function () use ($service, $data, $supplies) {
                $updated = $this->repo->update($service, $data);
                $this->repo->syncSupplies($updated, $supplies);

                return $updated;
            });
        } catch (\Throwable $exception) {
            Storage::disk('public')->delete($newPaths);

            throw $exception;
        }

        if ($removed !== []) {
            Storage::disk('public')->delete($removed);
        }

        return $updated;
    }

    public function delete(ShopService $service): void
    {
        $this->repo->delete($service);
    }

    public function toggleActive(ShopService $service): ShopService
    {
        return $this->repo->update($service, [
            'is_active' => ! $service->is_active,
        ]);
    }

    public function restore(ShopService $service): void
    {
        $service->restore();
    }

    public function restoreAll(int $shopId): void
    {
        ShopService::onlyTrashed()->where('shop_id', $shopId)->restore();
    }

    // ─── Private ───────────────────────────────────────

    /**
     * The supply lines to keep (none when "Uses supplies" is off) and the rest of the service data.
     * Each line's unit is always the inventory item's consumption unit, so a service's consumption
     * is measured the same way its stock is.
     *
     * @return array{0: array, 1: array}
     */
    private function splitSupplies(array $data, int $shopId): array
    {
        $supplies = ! empty($data['uses_supplies']) ? ($data['supplies'] ?? []) : [];
        unset($data['uses_supplies'], $data['supplies']);

        if ($supplies !== []) {
            $units = $this->inventory->all($shopId)->pluck('unit', 'id');
            $supplies = array_map(
                fn (array $line) => [...$line, 'unit' => (string) ($units[(int) $line['inventory_id']] ?? '')],
                $supplies,
            );
        }

        return [$data, $supplies];
    }

    private function createWithSupplies(array $data, array $supplies): ShopService
    {
        return DB::transaction(function () use ($data, $supplies) {
            $service = $this->repo->create($data);
            $this->repo->syncSupplies($service, $supplies);

            return $service;
        });
    }

    /**
     * @param  list<UploadedFile>  $photos
     * @return list<string>
     */
    private function storePhotos(array $photos, int $shopId): array
    {
        $paths = [];

        try {
            foreach ($photos as $photo) {
                $paths[] = $photo->store("service-photos/{$shopId}", 'public');
            }
        } catch (\Throwable $exception) {
            Storage::disk('public')->delete($paths);

            throw $exception;
        }

        return $paths;
    }

    /**
     * Both photo columns: every path, and the first again as the cover for places that show one.
     *
     * @param  list<string>  $paths
     */
    private function photoColumns(array $paths): array
    {
        return ['photo_paths' => $paths === [] ? null : $paths, 'photo_path' => $paths[0] ?? null];
    }

    private function assertPhotoCount(int $count): void
    {
        if ($count > ShopService::MAX_PHOTOS) {
            throw ValidationException::withMessages([
                'photos' => 'A service can have at most '.ShopService::MAX_PHOTOS.' photos. Remove one before adding another.',
            ]);
        }
    }

    private function validatePricingFields(array $data): void
    {
        $model = $data['pricing_model'] ?? null;

        if ($model === 'per_kg' && empty($data['price_per_kg'])) {
            throw ValidationException::withMessages([
                'price_per_kg' => 'Price per kg is required for per-kg pricing.',
            ]);
        }

        if ($model === 'per_piece' && empty($data['price_per_piece'])) {
            throw ValidationException::withMessages([
                'price_per_piece' => 'Price per piece is required for per-piece pricing.',
            ]);
        }

        if ($model === 'per_bundle') {
            if (empty($data['bundle_price'])) {
                throw ValidationException::withMessages([
                    'bundle_price' => 'Bundle price is required for bundle pricing.',
                ]);
            }
            if (empty($data['bundle_weight_kg'])) {
                throw ValidationException::withMessages([
                    'bundle_weight_kg' => 'Bundle weight is required for bundle pricing.',
                ]);
            }
        }
    }
}
