<?php

use App\Enums\AccountType;
use App\Enums\EmploymentType;
use App\Enums\PayBasis;
use App\Http\Requests\Shop\Employee\StoreEmployeeRequest;
use App\Models\Employee;
use App\Models\Shop;
use App\Models\User;
use App\Repositories\EmployeeRepository;
use Illuminate\Support\Facades\Validator;

function createEmployeeRepositoryTestShop(User $owner): Shop
{
    return Shop::create([
        'owner_id' => $owner->id,
        'shop_name' => 'Employee Repository Test Laundry',
        'phone' => '09171234567',
        'municipality' => 'Imus',
        'barangay' => 'Anabu I-A',
        'postal_code' => '4103',
        'status' => 'active',
    ]);
}

function createRepositoryTestEmployee(Shop $shop, array $attributes): Employee
{
    return Employee::create(array_merge([
        'shop_id' => $shop->id,
        'email' => fake()->unique()->safeEmail(),
        'position' => 'Staff',
        'employment_type' => 'full_time',
        'pay_rate' => 18000,
        'pay_basis' => 'monthly',
        'hire_date' => '2026-01-01',
        'status' => 'Active',
    ], $attributes));
}

test('employee pagination searches filters sorts and preserves staff scope', function () {
    $owner = User::factory()->create([
        'name' => 'Mike Owner',
        'role' => AccountType::ShopOwner->value,
    ]);
    $staffUser = User::factory()->create(['role' => AccountType::Staff->value]);
    $shop = createEmployeeRepositoryTestShop($owner);

    createRepositoryTestEmployee($shop, [
        'employee_id' => 'EMP-001',
        'first_name' => 'John',
        'last_name' => 'Santos',
        'branch_name' => 'Imus Branch',
        'phone' => '09170000001',
        'created_by' => $owner->id,
    ]);
    createRepositoryTestEmployee($shop, [
        'employee_id' => 'EMP-002',
        'first_name' => 'Maria',
        'last_name' => 'Cruz',
        'branch_name' => 'Bacoor Branch',
        'phone' => '09170000002',
        'employment_type' => 'part_time',
        'created_by' => $owner->id,
    ]);
    createRepositoryTestEmployee($shop, [
        'user_id' => $staffUser->id,
        'employee_id' => 'EMP-003',
        'first_name' => 'Current',
        'last_name' => 'Staff',
        'branch_name' => 'Imus Branch',
        'created_by' => $owner->id,
    ]);
    createRepositoryTestEmployee($shop, [
        'employee_id' => 'EMP-004',
        'first_name' => 'Inactive',
        'last_name' => 'Worker',
        'branch_name' => 'Imus Branch',
        'status' => 'Inactive',
        'created_by' => $owner->id,
    ]);

    $repository = app(EmployeeRepository::class);
    $filtered = $repository->paginateForShop($shop, [
        'search' => 'John Santos',
        'status' => 'Active',
        'branch' => 'Imus Branch',
        'sort_by' => 'full_name',
        'sort_direction' => 'asc',
        'per_page' => 5,
    ]);

    expect($filtered->total())->toBe(1)
        ->and($filtered->first()->employee_id)->toBe('EMP-001');

    $partTime = $repository->paginateForShop($shop, [
        'employment_type' => 'part_time',
        'sort_by' => 'employment_type',
        'sort_direction' => 'asc',
        'per_page' => 5,
    ]);

    expect($partTime->total())->toBe(1)
        ->and($partTime->first()->employee_id)->toBe('EMP-002');

    $staffScoped = $repository->paginateForShop(
        $shop,
        [
            'sort_by' => 'employee_id',
            'sort_direction' => 'asc',
            'per_page' => 5,
        ],
        'Imus Branch',
        $staffUser->id,
    );

    expect($staffScoped->total())->toBe(2)
        ->and($staffScoped->pluck('employee_id')->all())
        ->toBe(['EMP-001', 'EMP-004']);
});

test('active manager options include only active managers from the shop', function () {
    $owner = User::factory()->create(['role' => AccountType::ShopOwner->value]);
    $otherOwner = User::factory()->create(['role' => AccountType::ShopOwner->value]);
    $shop = createEmployeeRepositoryTestShop($owner);
    $otherShop = createEmployeeRepositoryTestShop($otherOwner);

    createRepositoryTestEmployee($shop, [
        'employee_id' => 'MGR-001',
        'first_name' => 'Alice',
        'last_name' => 'Manager',
        'position' => 'Manager',
    ]);
    createRepositoryTestEmployee($shop, [
        'employee_id' => 'MGR-002',
        'first_name' => 'Inactive',
        'last_name' => 'Manager',
        'position' => 'Manager',
        'status' => 'Inactive',
    ]);
    createRepositoryTestEmployee($shop, [
        'employee_id' => 'STF-001',
        'first_name' => 'Regular',
        'last_name' => 'Staff',
        'position' => 'Staff',
    ]);
    createRepositoryTestEmployee($otherShop, [
        'employee_id' => 'MGR-003',
        'first_name' => 'Other',
        'last_name' => 'Manager',
        'position' => 'Manager',
    ]);

    $managers = app(EmployeeRepository::class)
        ->activeManagerOptionsForShop($shop->id);

    expect($managers)->toHaveCount(1)
        ->and($managers[0]['employee_id'])->toBe('MGR-001')
        ->and($managers[0]['name'])->toBe('Alice Manager');
});

test('employee employment types are restricted to the supported options', function (string $type) {
    $rules = (new StoreEmployeeRequest)->rules();
    $validator = Validator::make(
        ['employment_type' => $type],
        ['employment_type' => $rules['employment_type']],
    );

    expect($validator->passes())->toBeTrue();
})->with(array_map(
    fn (EmploymentType $type) => $type->value,
    EmploymentType::cases(),
));

test('an invalid employee employment type is rejected', function () {
    $rules = (new StoreEmployeeRequest)->rules();
    $validator = Validator::make(
        ['employment_type' => 'freelance'],
        ['employment_type' => $rules['employment_type']],
    );

    expect($validator->fails())->toBeTrue();
});

test('employee pay bases are restricted to the supported options', function (string $basis) {
    $rules = (new StoreEmployeeRequest)->rules();
    $validator = Validator::make(
        ['pay_basis' => $basis],
        ['pay_basis' => $rules['pay_basis']],
    );

    expect($validator->passes())->toBeTrue();
})->with(array_map(
    fn (PayBasis $basis) => $basis->value,
    PayBasis::cases(),
));

test('an invalid employee pay basis is rejected', function () {
    $rules = (new StoreEmployeeRequest)->rules();
    $validator = Validator::make(
        ['pay_basis' => 'weekly'],
        ['pay_basis' => $rules['pay_basis']],
    );

    expect($validator->fails())->toBeTrue();
});
