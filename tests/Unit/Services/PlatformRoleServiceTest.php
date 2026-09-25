<?php

use App\Exceptions\ProtectedPlatformRoleException;
use App\Models\PlatformRole;
use App\Repositories\Contracts\PlatformRoleRepositoryInterface;
use App\Services\PlatformRoleService;
use Illuminate\Support\Collection;
use Mockery\MockInterface;

afterEach(fn () => Mockery::close());

test('service creates a custom role through the repository', function () {
    $role = new PlatformRole([
        'name' => 'Support Agent',
        'slug' => 'support_agent',
        'description' => 'Handles support.',
        'is_system' => false,
    ]);
    $role->public_id = '01TESTROLE0000000000000000';
    $role->setRelation('permissions', new Collection);

    $repository = Mockery::mock(PlatformRoleRepositoryInterface::class, function (MockInterface $mock) use ($role) {
        $mock->shouldReceive('create')
            ->once()
            ->with([
                'name' => 'Support Agent',
                'slug' => 'support_agent',
                'description' => 'Handles support.',
                'is_system' => false,
            ])
            ->andReturn($role);
        $mock->shouldReceive('loadPermissions')
            ->once()
            ->with($role)
            ->andReturn($role);
    });

    $result = (new PlatformRoleService($repository))->createRole([
        'name' => ' Support Agent ',
        'role_slug' => 'support_agent',
        'description' => 'Handles support.',
    ]);

    expect($result)
        ->name->toBe('Support Agent')
        ->slug->toBe('support_agent')
        ->permissions->toBe([]);
});

test('service prevents changing super admin permissions before persistence', function () {
    $role = new PlatformRole([
        'name' => 'Super Admin',
        'slug' => 'super_admin',
        'is_system' => true,
    ]);
    $repository = Mockery::mock(PlatformRoleRepositoryInterface::class);
    $repository->shouldNotReceive('permissionExists');
    $repository->shouldNotReceive('grantPermission');
    $repository->shouldNotReceive('revokePermission');

    expect(fn () => (new PlatformRoleService($repository))
        ->togglePermission($role, 'admin.roles'))
        ->toThrow(ProtectedPlatformRoleException::class);
});

test('service prevents deleting a system role before persistence', function () {
    $role = new PlatformRole([
        'name' => 'Shop Owner',
        'slug' => 'owner',
        'is_system' => true,
    ]);
    $repository = Mockery::mock(PlatformRoleRepositoryInterface::class);
    $repository->shouldNotReceive('delete');

    expect(fn () => (new PlatformRoleService($repository))->deleteRole($role))
        ->toThrow(ProtectedPlatformRoleException::class);
});
