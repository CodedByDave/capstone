<?php

use App\Models\IssueReport;
use App\Repositories\IssueReportRepository;
use App\Services\IssueReportService;
use Illuminate\Support\Carbon;
use Mockery\MockInterface;

afterEach(function () {
    Carbon::setTestNow();
    Mockery::close();
});

test('resolving an issue records its resolution time through the repository', function () {
    Carbon::setTestNow('2026-09-26 14:30:00');
    $report = new IssueReport([
        'status' => 'open',
        'priority' => 'medium',
    ]);
    $repository = Mockery::mock(IssueReportRepository::class, function (MockInterface $mock) use ($report) {
        $mock->shouldReceive('updateReport')
            ->once()
            ->with($report, Mockery::on(fn (array $attributes) => $attributes['status'] === 'resolved'
                && $attributes['priority'] === 'high'
                && $attributes['admin_notes'] === 'Fixed.'
                && $attributes['resolved_at']->equalTo(now())));
    });

    (new IssueReportService($repository))->update($report, [
        'status' => 'resolved',
        'priority' => 'high',
        'admin_notes' => 'Fixed.',
    ]);
});

test('reopening an issue clears its resolution time', function () {
    $report = new IssueReport;
    $report->setRawAttributes([
        'status' => 'resolved',
        'priority' => 'high',
        'resolved_at' => Carbon::parse('2026-09-25 10:00:00'),
    ]);
    $repository = Mockery::mock(IssueReportRepository::class, function (MockInterface $mock) use ($report) {
        $mock->shouldReceive('updateReport')
            ->once()
            ->with($report, Mockery::on(fn (array $attributes) => $attributes['resolved_at'] === null));
    });

    (new IssueReportService($repository))->update($report, [
        'status' => 'in_progress',
        'priority' => 'high',
        'admin_notes' => null,
    ]);
});
