<?php

use App\Repositories\AuditLogRepository;
use App\Services\AuditLogService;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Mockery\MockInterface;

afterEach(fn () => Mockery::close());

test('audit log service assembles index data through the repository', function () {
    $filters = [
        'category' => 'authentication',
        'sort_by' => 'occurred_at',
        'sort_direction' => 'desc',
        'per_page' => '25',
    ];
    $paginator = Mockery::mock(LengthAwarePaginator::class);
    $modules = collect(['Authentication', 'User Management']);
    $repository = Mockery::mock(AuditLogRepository::class, function (MockInterface $mock) use ($filters, $modules, $paginator) {
        $mock->shouldReceive('getPaginated')
            ->once()
            ->with($filters, 25)
            ->andReturn($paginator);
        $mock->shouldReceive('getStats')
            ->once()
            ->andReturn(['total' => 4, 'archived' => 1]);
        $mock->shouldReceive('getModules')
            ->once()
            ->andReturn($modules);
    });

    $data = (new AuditLogService($repository))->getIndexData($filters);

    expect($data)
        ->toBe([
            'logs' => $paginator,
            'stats' => ['total' => 4, 'archived' => 1],
            'modules' => $modules,
            'filters' => $filters,
        ]);
});

test('audit log mutations are delegated to the repository', function () {
    $entries = [
        ['category' => 'authentication', 'id' => 10],
        ['category' => 'activity', 'id' => 20],
    ];
    $repository = Mockery::mock(AuditLogRepository::class, function (MockInterface $mock) use ($entries) {
        $mock->shouldReceive('archive')->once()->with('activity', 20);
        $mock->shouldReceive('bulkArchive')->once()->with($entries);
        $mock->shouldReceive('restore')->once()->with('authentication', 10);
        $mock->shouldReceive('bulkRestore')->once()->with($entries);
        $mock->shouldReceive('forceDelete')->once()->with('activity', 20);
    });
    $service = new AuditLogService($repository);

    $service->archive('activity', 20);
    $service->bulkArchive($entries);
    $service->restore('authentication', 10);
    $service->bulkRestore($entries);
    $service->forceDelete('activity', 20);
});
