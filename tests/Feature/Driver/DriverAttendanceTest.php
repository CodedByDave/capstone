<?php

use App\Models\Attendance;
use App\Models\EmployeeSchedule;
use App\Models\Rider;
use Inertia\Testing\AssertableInertia as Assert;

// driverShop() and onlineRider() come from DriverAppTest.php and tests/Pest.php.

function scheduleFor(Rider $rider, ?string $day = null, string $start = '08:00', string $end = '17:00'): EmployeeSchedule
{
    return EmployeeSchedule::create([
        'employee_id' => $rider->employee_id,
        'day' => $day ?? now()->format('l'),
        'start_time' => $start,
        'end_time' => $end,
    ]);
}

function atShop(array $overrides = []): array
{
    return array_merge(['latitude' => 14.43, 'longitude' => 120.93], $overrides);
}

test('the home page shows today\'s shift and lets a scheduled driver clock in', function () {
    $rider = onlineRider(driverShop(), scheduled: false);
    scheduleFor($rider);

    $this->actingAs($rider->employee->user)->get('/driver')
        ->assertInertia(fn (Assert $page) => $page
            ->where('attendance.is_work_day', true)
            ->where('attendance.shift.start', '08:00')
            ->where('attendance.shift.end', '17:00')
            ->where('attendance.blocked_reason', null)
            ->where('attendance.has_clocked_in', false));
});

test('a scheduled driver at the shop clocks in as present, then out', function () {
    $rider = onlineRider(driverShop(), scheduled: false);
    scheduleFor($rider);
    $user = $rider->employee->user;

    $this->actingAs($user)->post('/driver/attendance/clock-in', atShop())
        ->assertRedirect()
        ->assertSessionHas('toast.type', 'success');

    $record = Attendance::where('employee_id', $rider->employee_id)->first();
    expect($record->status)->toBe('present')->and($record->time_in)->not->toBeNull();

    $this->actingAs($user)->post('/driver/attendance/clock-out', atShop())
        ->assertSessionHas('toast.type', 'success');

    expect($record->fresh()->time_out)->not->toBeNull();
});

test('a driver cannot clock in on a day that is not in their schedule', function () {
    $rider = onlineRider(driverShop(), scheduled: false);
    scheduleFor($rider, now()->addDay()->format('l'));

    $this->actingAs($rider->employee->user)->post('/driver/attendance/clock-in', atShop())
        ->assertSessionHas('toast.type', 'error');

    expect(Attendance::where('employee_id', $rider->employee_id)->exists())->toBeFalse();

    $this->actingAs($rider->employee->user)->get('/driver')
        ->assertInertia(fn (Assert $page) => $page
            ->where('attendance.has_schedule', true)
            ->where('attendance.is_work_day', false)
            ->where('attendance.blocked_reason', now()->format('l').' is not in your work schedule.'));
});

test('a driver with no schedule at all cannot clock in', function () {
    $rider = onlineRider(driverShop(), scheduled: false);

    $this->actingAs($rider->employee->user)->post('/driver/attendance/clock-in', atShop())
        ->assertSessionHas('toast.type', 'error');

    expect(Attendance::where('employee_id', $rider->employee_id)->exists())->toBeFalse();

    $this->actingAs($rider->employee->user)->get('/driver')
        ->assertInertia(fn (Assert $page) => $page->where('attendance.has_schedule', false));
});

test('clocking in without a location is refused and nothing is saved', function () {
    $rider = onlineRider(driverShop(), scheduled: false);
    scheduleFor($rider);

    $this->actingAs($rider->employee->user)->post('/driver/attendance/clock-in', [])
        ->assertSessionHasErrors(['latitude', 'longitude']);

    $this->actingAs($rider->employee->user)->post('/driver/attendance/clock-in', ['latitude' => null, 'longitude' => null])
        ->assertSessionHasErrors(['latitude', 'longitude']);

    expect(Attendance::where('employee_id', $rider->employee_id)->exists())->toBeFalse();
});

test('a location outside the world is refused', function () {
    $rider = onlineRider(driverShop(), scheduled: false);
    scheduleFor($rider);

    $this->actingAs($rider->employee->user)
        ->post('/driver/attendance/clock-in', ['latitude' => 120, 'longitude' => 300])
        ->assertSessionHasErrors(['latitude', 'longitude']);
});

test('a driver far from the shop is marked absent like other staff', function () {
    $rider = onlineRider(driverShop(), scheduled: false);
    scheduleFor($rider);

    $this->actingAs($rider->employee->user)
        ->post('/driver/attendance/clock-in', ['latitude' => 14.60, 'longitude' => 121.10])
        ->assertSessionHas('toast.type', 'error');

    $record = Attendance::where('employee_id', $rider->employee_id)->first();
    expect($record->status)->toBe('absent')->and($record->time_in)->toBeNull();
});

test('a driver cannot clock in twice on the same day', function () {
    $rider = onlineRider(driverShop(), scheduled: false);
    scheduleFor($rider);
    $user = $rider->employee->user;

    $this->actingAs($user)->post('/driver/attendance/clock-in', atShop());
    $this->actingAs($user)->post('/driver/attendance/clock-in', atShop())
        ->assertSessionHas('toast.message', 'You have already clocked in today.');

    expect(Attendance::where('employee_id', $rider->employee_id)->count())->toBe(1);
});

test('a driver cannot clock out before clocking in or twice', function () {
    $rider = onlineRider(driverShop(), scheduled: false);
    scheduleFor($rider);
    $user = $rider->employee->user;

    $this->actingAs($user)->post('/driver/attendance/clock-out', atShop())
        ->assertSessionHas('toast.message', 'You have not clocked in yet today.');

    $this->actingAs($user)->post('/driver/attendance/clock-in', atShop());
    $this->actingAs($user)->post('/driver/attendance/clock-out', atShop());
    $this->actingAs($user)->post('/driver/attendance/clock-out', atShop())
        ->assertSessionHas('toast.message', 'You have already clocked out today.');
});

test('clocking out does not need a location', function () {
    $rider = onlineRider(driverShop(), scheduled: false);
    scheduleFor($rider);
    $user = $rider->employee->user;

    $this->actingAs($user)->post('/driver/attendance/clock-in', atShop());
    $this->actingAs($user)->post('/driver/attendance/clock-out', [])
        ->assertSessionHas('toast.type', 'success');
});

test('a driver only ever clocks in as themselves, never as another driver', function () {
    $shop = driverShop();
    $mine = onlineRider($shop, 'Mine');
    $other = onlineRider($shop, 'Other');
    scheduleFor($mine);
    scheduleFor($other);

    $this->actingAs($mine->employee->user)->post('/driver/attendance/clock-in', atShop([
        'employee_id' => $other->employee_id,
    ]));

    expect(Attendance::where('employee_id', $mine->employee_id)->count())->toBe(1)
        ->and(Attendance::where('employee_id', $other->employee_id)->count())->toBe(0);
});

test('someone who is not a driver cannot use the driver attendance routes', function () {
    $this->post('/driver/attendance/clock-in', atShop())->assertRedirect();

    $shop = driverShop();
    $staff = App\Models\User::factory()->create(['role' => App\Enums\AccountType::Staff->value, 'shop_id' => $shop->id]);

    $this->actingAs($staff)->post('/driver/attendance/clock-in', atShop())->assertNotFound();
});

test('the profile lists the driver\'s own attendance for the last 14 days only', function () {
    $shop = driverShop();
    $rider = onlineRider($shop, 'Mine');
    $other = onlineRider($shop, 'Other');

    Attendance::create(['employee_id' => $rider->employee_id, 'shop_id' => $shop->id, 'marked_by' => $rider->employee->user_id, 'date' => now()->subDay()->toDateString(), 'status' => 'present', 'time_in' => '08:01:00', 'time_out' => '17:00:00']);
    Attendance::create(['employee_id' => $rider->employee_id, 'shop_id' => $shop->id, 'marked_by' => $rider->employee->user_id, 'date' => now()->subDays(30)->toDateString(), 'status' => 'present', 'time_in' => '08:00:00']);
    Attendance::create(['employee_id' => $other->employee_id, 'shop_id' => $shop->id, 'marked_by' => $rider->employee->user_id, 'date' => now()->subDay()->toDateString(), 'status' => 'absent']);

    $this->actingAs($rider->employee->user)->get('/driver/profile')
        ->assertInertia(fn (Assert $page) => $page
            ->component('driver/Profile')
            ->has('attendance_history', 1)
            ->where('attendance_history.0.status', 'present'));
});
