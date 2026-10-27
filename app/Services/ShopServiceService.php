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

    public function create(array $data, ?UploadedFile $photo = null): ShopService
    {
        $this->validatePricingFields($data);
        [$data, $supplies] = $this->splitSupplies($data);

        if ($photo === null) {
            return $this->createWithSupplies($data, $supplies);
        }

        $path = $this->storePhoto($photo, (int) $data['shop_id']);

        try {
            return $this->createWithSupplies([...$data, 'photo_path' => $path], $supplies);
        } catch (\Throwable $exception) {
            Storage::disk('public')->delete($path);

            throw $exception;
        }
    }

    public function update(ShopService $service, array $data, ?UploadedFile $photo = null, bool $removePhoto = false): ShopService
    {
        $this->validatePricingFields($data);
        [$data, $supplies] = $this->splitSupplies($data);

        $oldPath = $service->photo_path;
        $newPath = null;

        if ($photo !== null) {
            $newPath = $this->storePhoto($photo, $service->shop_id);
            $data['photo_path'] = $newPath;
        } elseif ($removePhoto) {
            $data['photo_path'] = null;
        }

        try {
            $updated = DB::transaction(function () use ($service, $data, $supplies) {
                $updated = $this->repo->update($service, $data);
                $this->repo->syncSupplies($updated, $supplies);

                return $updated;
            });
        } catch (\Throwable $exception) {
            if ($newPath !== null) {
                Storage::disk('public')->delete($newPath);
            }

            throw $exception;
        }

        if ($oldPath && array_key_exists('photo_path', $data)) {
            Storage::disk('public')->delete($oldPath);
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
     *
     * @return array{0: array, 1: array}
     */
    private function splitSupplies(array $data): array
    {
        $supplies = ! empty($data['uses_supplies']) ? ($data['supplies'] ?? []) : [];
        unset($data['uses_supplies'], $data['supplies']);

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

    private function storePhoto(UploadedFile $photo, int $shopId): string
    {
        return $photo->store("service-photos/{$shopId}", 'public');
    }

    private function validatePricingFields(array $data): void
    {
        $model = $data['pricing_model'] ?? null;

        if ($model === 'per_kg' && empty($data['price_per_kg'])) {
            throw ValidationException::withMessages([
                'price_per_kg' => 'Price per kg is required for per-kg pricing.',
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
