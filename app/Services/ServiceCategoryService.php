<?php

namespace App\Services;

use App\Models\ServiceCategory;
use App\Models\ShopService;
use App\Repositories\ServiceCategoryRepository;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

/** The categories a shop can file its services under: the built-in ones plus its own. */
class ServiceCategoryService
{
    public function __construct(private readonly ServiceCategoryRepository $repository) {}

    /** @return list<array{value: string, label: string, description: string|null}> */
    public function customFor(int $shopId): array
    {
        return $this->repository->allByShop($shopId)
            ->map(fn (ServiceCategory $c) => ['value' => $c->key, 'label' => $c->name, 'description' => $c->description])
            ->all();
    }

    /** @return list<string> Every category key a service of this shop may use. */
    public function allowedKeys(int $shopId): array
    {
        return [...ShopService::CATEGORIES, ...$this->repository->keysByShop($shopId)];
    }

    /** Display name of a custom category key, or null for a built-in one. */
    public function labelFor(int $shopId, string $key): ?string
    {
        return $this->repository->findByKey($shopId, $key)?->name;
    }

    public function create(int $shopId, array $data): ServiceCategory
    {
        $name = trim($data['name']);

        if ($this->repository->nameExists($shopId, $name)) {
            throw ValidationException::withMessages(['name' => 'You already have a category with this name.']);
        }

        $base = 'custom_'.Str::slug($name, '_');
        $key = $base;

        for ($i = 2; $this->repository->keyExists($shopId, $key); $i++) {
            $key = "{$base}_{$i}";
        }

        return $this->repository->create([
            'shop_id' => $shopId,
            'key' => $key,
            'name' => $name,
            'description' => $data['description'] ?? null,
        ]);
    }

    /**
     * @throws ModelNotFoundException
     * @throws ValidationException
     */
    public function delete(int $shopId, string $key): void
    {
        $category = $this->repository->findByKey($shopId, $key)
            ?? throw (new ModelNotFoundException)->setModel(ServiceCategory::class);

        if ($this->repository->servicesUsing($shopId, $key) > 0) {
            throw ValidationException::withMessages([
                'category' => 'This category is used by a service. Move those services to another category first.',
            ]);
        }

        $this->repository->delete($category);
    }
}
