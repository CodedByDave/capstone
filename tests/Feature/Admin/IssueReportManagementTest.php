<?php

use App\Enums\AccountType;
use App\Models\IssueReport;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;
use Inertia\Testing\AssertableInertia as Assert;

test('super admin can view and filter issue reports', function () {
    $admin = User::factory()->create(['role' => AccountType::SuperAdmin->value]);
    $reporter = User::factory()->create(['name' => 'Issue Reporter']);
    $matchingReport = IssueReport::create([
        'user_id' => $reporter->id,
        'subject' => 'Checkout button is not working',
        'description' => 'The checkout button does not respond on mobile.',
        'category' => 'bug',
        'priority' => 'critical',
        'status' => 'open',
    ]);
    IssueReport::create([
        'user_id' => $reporter->id,
        'subject' => 'Please add another report format',
        'description' => 'A feature request for reporting.',
        'category' => 'feature_request',
        'priority' => 'low',
        'status' => 'resolved',
        'resolved_at' => now(),
    ]);

    $this->actingAs($admin)
        ->get(route('admin.issue-reports.index', [
            'category' => 'bug',
            'priority' => 'critical',
            'status' => 'open',
        ]))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('admin/issues/Index')
            ->where('stats.total', 2)
            ->where('stats.open', 1)
            ->where('stats.resolved', 1)
            ->where('stats.critical', 1)
            ->where('reports.total', 1)
            ->where('reports.data.0.public_id', $matchingReport->public_id)
        );
});

test('super admin can update issue status priority and notes', function () {
    $admin = User::factory()->create(['role' => AccountType::SuperAdmin->value]);
    $report = IssueReport::create([
        'subject' => 'Account page error',
        'description' => 'The profile page shows an error.',
        'category' => 'account',
        'priority' => 'medium',
        'status' => 'open',
    ]);

    $this->actingAs($admin)
        ->patch(route('admin.issue-reports.update', $report->public_id), [
            'status' => 'resolved',
            'priority' => 'high',
            'admin_notes' => 'Confirmed fixed after deployment.',
        ])
        ->assertRedirect();

    $report->refresh();

    expect($report->status)->toBe('resolved')
        ->and($report->priority)->toBe('high')
        ->and($report->admin_notes)->toBe('Confirmed fixed after deployment.')
        ->and($report->resolved_at)->not->toBeNull();
});

test('issue reports can be sorted and exported as csv', function () {
    $admin = User::factory()->create(['role' => AccountType::SuperAdmin->value]);
    IssueReport::create([
        'subject' => 'Alpha issue',
        'description' => 'First issue.',
        'category' => 'bug',
        'priority' => 'low',
        'status' => 'open',
    ]);
    IssueReport::create([
        'subject' => 'Zulu issue',
        'description' => 'Second issue.',
        'category' => 'billing',
        'priority' => 'critical',
        'status' => 'in_progress',
    ]);

    $this->actingAs($admin)
        ->get(route('admin.issue-reports.index', [
            'sort_by' => 'subject',
            'sort_direction' => 'desc',
        ]))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->where('reports.data.0.subject', 'Zulu issue')
            ->where('filters.sort_by', 'subject')
        );

    $response = $this->actingAs($admin)->get(route('admin.issue-reports.export'));
    $response->assertOk();
    expect($response->headers->get('content-type'))->toContain('text/csv')
        ->and($response->streamedContent())->toContain('public_id')->toContain('Alpha issue');
});

test('issue report csv backup can be imported without creating duplicates', function () {
    $admin = User::factory()->create(['role' => AccountType::SuperAdmin->value]);
    $reporter = User::factory()->create(['email' => 'issue-backup@example.test']);
    $publicId = (string) Str::ulid();
    $csv = implode("\n", [
        'public_id,reporter_email,reporter_name,subject,description,category,priority,status,page_url,browser,admin_notes,resolved_at,reported_at',
        "{$publicId},{$reporter->email},Backup Reporter,Imported issue,Imported description,bug,high,open,/checkout,Chrome,Investigate,,2026-09-20 09:00:00",
    ]);

    foreach ([1, 2] as $attempt) {
        $this->actingAs($admin)
            ->post(route('admin.issue-reports.import'), [
                'file' => UploadedFile::fake()->createWithContent("issues-{$attempt}.csv", $csv),
            ])
            ->assertRedirect();
    }

    $this->assertDatabaseCount('issue_reports', 1);
    $this->assertDatabaseHas('issue_reports', [
        'public_id' => $publicId,
        'user_id' => $reporter->id,
        'subject' => 'Imported issue',
        'priority' => 'high',
    ]);
});
