<?php

use App\Enums\AccountType;
use App\Models\ActivityLog;
use App\Models\LoginLog;
use App\Models\Shop;
use App\Models\User;
use App\Services\AuditLogService;
use Illuminate\Http\UploadedFile;
use Inertia\Testing\AssertableInertia as Assert;

test('the audit feed combines authentication and user activity events', function () {
    $admin = User::factory()->create([
        'role' => AccountType::SuperAdmin->value,
        'name' => 'Audit Admin',
    ]);
    $owner = User::factory()->create([
        'role' => AccountType::ShopOwner->value,
    ]);
    $shop = Shop::create([
        'owner_id' => $owner->id,
        'shop_name' => 'Audit Test Laundry',
        'phone' => '09171234567',
        'municipality' => 'Manila',
        'barangay' => 'Test Barangay',
        'postal_code' => '1000',
    ]);

    LoginLog::create([
        'user_id' => $owner->id,
        'email' => $owner->email,
        'name' => $owner->name,
        'role' => $owner->role,
        'ip_address' => '127.0.0.1',
        'user_agent' => 'Test Browser',
        'status' => 'success',
        'logged_at' => now(),
    ]);

    ActivityLog::create([
        'module' => 'Employee',
        'action' => 'created',
        'performed_by' => $owner->id,
        'shop_id' => $shop->id,
        'changes' => ['name' => ['old' => null, 'new' => 'Test Employee']],
    ]);

    $logs = app(AuditLogService::class)->getPaginated();
    $categories = collect($logs->items())->pluck('category');

    expect($categories)
        ->toContain('authentication')
        ->toContain('activity');

    $this->actingAs($admin)
        ->get(route('admin.audit-logs.index'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('admin/logs/Index')
            ->where('stats.authentication', 1)
            ->where('stats.activities', 1)
            ->has('logs.data', 2)
        );
});

test('audit logs can be filtered and exported as csv', function () {
    $admin = User::factory()->create([
        'role' => AccountType::SuperAdmin->value,
    ]);

    LoginLog::create([
        'user_id' => $admin->id,
        'email' => $admin->email,
        'name' => $admin->name,
        'role' => $admin->role,
        'status' => 'failed',
        'failure_reason' => 'Invalid credentials',
        'logged_at' => now(),
    ]);

    $this->actingAs($admin)
        ->get(route('admin.audit-logs.export', ['category' => 'authentication']))
        ->assertOk()
        ->assertHeader('content-type', 'text/csv; charset=UTF-8');
});

test('an audit csv backup can be imported without creating duplicates', function () {
    $admin = User::factory()->create([
        'role' => AccountType::SuperAdmin->value,
        'name' => 'Audit Import Admin',
    ]);
    $csv = implode("\n", [
        'type,user,email,role,module,event,shop,ip_address,user_agent,details,occurred_at',
        "authentication,{$admin->name},{$admin->email},super_admin,Authentication,success,,127.0.0.1,Chrome,,2026-09-20 10:00:00",
        "activity,{$admin->name},{$admin->email},super_admin,User Management,updated,,,,,2026-09-20 10:01:00",
    ]);

    $this->actingAs($admin)
        ->post(route('admin.audit-logs.import'), [
            'file' => UploadedFile::fake()->createWithContent('audit-backup.csv', $csv),
        ])
        ->assertRedirect();

    $this->assertDatabaseHas('login_logs', [
        'email' => $admin->email,
        'status' => 'success',
        'logged_at' => '2026-09-20 10:00:00',
    ]);
    $this->assertDatabaseHas('activity_logs', [
        'performed_by' => $admin->id,
        'module' => 'User Management',
        'action' => 'updated',
        'created_at' => '2026-09-20 10:01:00',
    ]);

    $this->actingAs($admin)
        ->post(route('admin.audit-logs.import'), [
            'file' => UploadedFile::fake()->createWithContent('audit-backup.csv', $csv),
        ])
        ->assertRedirect();

    expect(LoginLog::withTrashed()->count())->toBe(1)
        ->and(ActivityLog::withTrashed()->count())->toBe(1);
});

test('archiving a user creates an admin audit activity', function () {
    $admin = User::factory()->create([
        'role' => AccountType::SuperAdmin->value,
    ]);
    $user = User::factory()->create([
        'role' => AccountType::Customer->value,
    ]);

    $this->actingAs($admin)
        ->delete(route('admin.users.destroy', $user))
        ->assertRedirect();

    $this->assertSoftDeleted($user);
    $this->assertDatabaseHas('activity_logs', [
        'module' => 'User Management',
        'action' => 'archived',
        'subject_type' => User::class,
        'subject_id' => $user->id,
        'performed_by' => $admin->id,
        'shop_id' => null,
    ]);
});

test('activity audit rows can be archived and restored', function () {
    $admin = User::factory()->create([
        'role' => AccountType::SuperAdmin->value,
    ]);
    $owner = User::factory()->create([
        'role' => AccountType::ShopOwner->value,
    ]);
    $shop = Shop::create([
        'owner_id' => $owner->id,
        'shop_name' => 'Archive Test Laundry',
        'phone' => '09171234567',
        'municipality' => 'Manila',
        'barangay' => 'Test Barangay',
        'postal_code' => '1000',
    ]);
    $activity = ActivityLog::create([
        'module' => 'User Management',
        'action' => 'updated',
        'performed_by' => $admin->id,
        'shop_id' => $shop->id,
        'changes' => ['status' => ['old' => 'pending', 'new' => 'active']],
    ]);

    $this->actingAs($admin)
        ->get(route('admin.audit-logs.show', [
            'category' => 'activity',
            'id' => $activity->id,
        ]))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('admin/logs/Show')
            ->where('log.category', 'activity')
            ->where('log.record_id', $activity->id)
            ->where('log.module', 'User Management')
            ->where('log.event', 'updated')
        );

    $this->actingAs($admin)
        ->delete(route('admin.audit-logs.destroy', [
            'category' => 'activity',
            'id' => $activity->id,
        ]))
        ->assertRedirect();

    $this->assertSoftDeleted($activity);

    $this->actingAs($admin)
        ->post(route('admin.audit-logs.archive.restore', [
            'category' => 'activity',
            'id' => $activity->id,
        ]))
        ->assertRedirect();

    $this->assertNotSoftDeleted($activity);
});
