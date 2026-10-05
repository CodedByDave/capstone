<?php

use App\Enums\AccountType;
use App\Models\Branch;
use App\Models\Shop;
use App\Models\User;
use App\Repositories\BranchRepository;

function createBranchRepositoryTestShop(User $owner): Shop
{
    return Shop::create([
        'owner_id' => $owner->id,
        'shop_name' => 'Branch Repository Test Laundry',
        'phone' => '09171234567',
        'municipality' => 'Imus',
        'barangay' => 'Anabu I-A',
        'postal_code' => '4103',
        'status' => 'active',
    ]);
}

test('branch pagination searches filters and sorts the complete shop dataset', function () {
    $owner = User::factory()->create([
        'name' => 'Mike Owner',
        'role' => AccountType::ShopOwner->value,
    ]);
    $alice = User::factory()->create(['name' => 'Alice Manager']);
    $zoe = User::factory()->create(['name' => 'Zoe Manager']);
    $shop = createBranchRepositoryTestShop($owner);

    Branch::create([
        'shop_id' => $shop->id,
        'branch_code' => 'IM-001',
        'name' => 'Imus Branch',
        'phone' => '09170000001',
        'email' => 'imus@example.test',
        'manager_name' => 'Imus Manager',
        'address' => 'Anabu I-A, Imus, Cavite',
        'status' => 'Active',
        'created_by' => $zoe->id,
        'updated_by' => $zoe->id,
    ]);
    Branch::create([
        'shop_id' => $shop->id,
        'branch_code' => 'BC-001',
        'name' => 'Bacoor Branch',
        'phone' => '09170000002',
        'email' => 'bacoor@example.test',
        'manager_name' => 'Bacoor Manager',
        'address' => 'Molino, Bacoor, Cavite',
        'status' => 'Active',
        'created_by' => $alice->id,
        'updated_by' => $alice->id,
    ]);
    Branch::create([
        'shop_id' => $shop->id,
        'branch_code' => 'DV-001',
        'name' => 'Dasmarinas Branch',
        'phone' => '09170000003',
        'email' => 'dasma@example.test',
        'manager_name' => 'Dasma Manager',
        'address' => 'Dasmarinas, Cavite',
        'status' => 'Inactive',
        'created_by' => $owner->id,
        'updated_by' => $owner->id,
    ]);

    $repository = app(BranchRepository::class);
    $activeBranches = $repository->paginateForShop($shop->id, [
        'search' => 'Cavite',
        'status' => 'Active',
        'sort_by' => 'name',
        'sort_direction' => 'desc',
        'per_page' => 5,
    ]);

    expect($activeBranches->total())->toBe(2)
        ->and($activeBranches->pluck('name')->all())
        ->toBe(['Imus Branch', 'Bacoor Branch']);

    $byCreator = $repository->paginateForShop($shop->id, [
        'sort_by' => 'creator_name',
        'sort_direction' => 'asc',
        'per_page' => 5,
    ]);

    expect($byCreator->pluck('name')->all())
        ->toBe(['Bacoor Branch', 'Dasmarinas Branch', 'Imus Branch']);
});
