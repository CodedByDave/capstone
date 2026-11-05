<?php

use App\Http\Middleware\EnforceOwnerPlatformPermissions;
use App\Http\Middleware\EnsureCurrentBusinessAgreementAccepted;
use App\Models\Attendance;
use App\Models\Employee;
use App\Models\EmployeeSchedule;
use App\Services\AttendanceService;
use Illuminate\Support\Carbon;
use Inertia\Testing\AssertableInertia as Assert;

// driverShop() and onlineRider() come from tests/Pest.php.

beforeEach(function () {
    $this->withoutMiddleware([
        EnsureCurrentBusinessAgreementAccepted::class,
        EnforceOwnerPlatformPermissions::class,
    ]);
});

function nightShift(Employee $employee, string $day = 'Monday', string $start = '22:00', string $end = '06:00'): EmployeeSchedule
{
    return EmployeeSchedule::create(['employee_id' => $employee->id, 'day' => $day, 'start_time' => $start, 'end_time' => $end]);
}

/** A Monday in the shop's timezone, at the given time. */
function onMonday(string $time): Carbon
{
    return Carbon::parse("2026-11-02 {$time}");
}

function shopLocation(): array
{
    return ['latitude' => 14.43, 'longitude' => 120.93];
}

// ── Saving schedules ─────────────────────────────────────────────────────────

test('an owner can save an overnight shift for an employee', function () {
    $shop = driverShop();
    $employee = onlineRider($shop, scheduled: false)->employee;

    $this->actingAs($shop->owner)
        ->putJson("/shop/employee/{$employee->public_id}/schedule", ['schedules' => [
            ['day' => 'Monday', 'start_time' => '22:00', 'end_time' => '06:00'],
            ['day' => 'Tuesday', 'start_time' => '08:00', 'end_time' => '17:00'],
        ]])
        ->assertOk();

    $shifts = EmployeeSchedule::where('employee_id', $employee->id)->get()->keyBy('day');
    expect($shifts['Monday']->endsNextDay())->toBeTrue()
        ->and($shifts['Tuesday']->endsNextDay())->toBeFalse();
});

test('a shift cannot start and end at the same time, and a day has one shift', function () {
    $shop = driverShop();
    $employee = onlineRider($shop, scheduled: false)->employee;
    $url = "/shop/employee/{$employee->public_id}/schedule";

    $this->actingAs($shop->owner)
        ->putJson($url, ['schedules' => [['day' => 'Monday', 'start_time' => '08:00', 'end_time' => '08:00']]])
        ->assertJsonValidationErrors('schedules.0.end_time');

    $this->actingAs($shop->owner)
        ->putJson($url, ['schedules' => [
            ['day' => 'Monday', 'start_time' => '08:00', 'end_time' => '12:00'],
            ['day' => 'Monday', 'start_time' => '13:00', 'end_time' => '17:00'],
        ]])
        ->assertJsonValidationErrors('schedules.1.day');

    expect(EmployeeSchedule::where('employee_id', $employee->id)->count())->toBe(0);
});

test('an owner can add, change and remove a single shift', function () {
    $shop = driverShop();
    $employee = onlineRider($shop, scheduled: false)->employee;
    $url = "/shop/employee/{$employee->public_id}/schedule";

    $id = $this->actingAs($shop->owner)
        ->postJson($url, ['day' => 'Friday', 'start_time' => '20:00', 'end_time' => '04:00'])
        ->assertCreated()
        ->json('id');

    // A second shift on the same day is refused.
    $this->actingAs($shop->owner)
        ->postJson($url, ['day' => 'Friday', 'start_time' => '08:00', 'end_time' => '12:00'])
        ->assertJsonValidationErrors('day');

    $this->actingAs($shop->owner)
        ->putJson("{$url}/{$id}", ['day' => 'Saturday', 'start_time' => '21:00', 'end_time' => '05:00'])
        ->assertOk();
    expect(EmployeeSchedule::find($id))->day->toBe('Saturday');

    $this->actingAs($shop->owner)->deleteJson("{$url}/{$id}")->assertOk();
    expect(EmployeeSchedule::find($id))->toBeNull();
});

test('another shop cannot see or change an employee schedule', function () {
    $shop = driverShop();
    $employee = onlineRider($shop, scheduled: false)->employee;
    $shift = nightShift($employee);
    $other = driverShop(['shop_name' => 'Other Laundry']);
    $url = "/shop/employee/{$employee->public_id}/schedule";

    $this->actingAs($other->owner)->putJson($url, ['schedules' => []])->assertNotFound();
    $this->actingAs($other->owner)->postJson($url, ['day' => 'Friday', 'start_time' => '08:00', 'end_time' => '12:00'])->assertNotFound();
    $this->actingAs($other->owner)->deleteJson("{$url}/{$shift->id}")->assertNotFound();

    // A shift of a different employee of the same shop is not found under this employee either.
    $colleague = onlineRider($shop, 'Ben', scheduled: false)->employee;
    $this->actingAs($shop->owner)->deleteJson("/shop/employee/{$colleague->public_id}/schedule/{$shift->id}")->assertNotFound();

    expect($shift->fresh())->not->toBeNull();
});

// ── Attendance on overnight shifts ───────────────────────────────────────────

test('after midnight an overnight shift still belongs to the day it started', function () {
    $employee = onlineRider(driverShop(), scheduled: false)->employee;
    nightShift($employee);
    $service = app(AttendanceService::class);

    expect($service->workDayFor($employee, onMonday('23:00'))['date']->toDateString())->toBe('2026-11-02')
        ->and($service->workDayFor($employee, onMonday('23:00')->addHours(4))['date']->toDateString())->toBe('2026-11-02')
        // Once the shift has ended, Tuesday is its own day (with no shift here).
        ->and($service->workDayFor($employee, onMonday('23:00')->addHours(8))['date']->toDateString())->toBe('2026-11-03')
        ->and($service->workDayFor($employee, onMonday('23:00')->addHours(8))['shift'])->toBeNull();
});

test('a driver clocks into a night shift before midnight and out after it on one record', function () {
    $rider = onlineRider(driverShop(), scheduled: false);
    nightShift($rider->employee);
    $user = $rider->employee->user;

    $this->travelTo(onMonday('21:55'));
    $this->actingAs($user)->post('/driver/attendance/clock-in', shopLocation())->assertSessionHas('toast.type', 'success');

    $this->travelTo(onMonday('21:55')->addHours(8)->addMinutes(5));
    $this->actingAs($user)->post('/driver/attendance/clock-out', shopLocation())->assertSessionHas('toast.type', 'success');

    $record = Attendance::where('employee_id', $rider->employee_id)->sole();
    expect($record->date->toDateString())->toBe('2026-11-02')
        ->and($record->time_in)->not->toBeNull()
        ->and($record->time_out)->not->toBeNull();
});

test('clocking in late after midnight counts on the night the shift started', function () {
    $rider = onlineRider(driverShop(), scheduled: false);
    nightShift($rider->employee);
    $user = $rider->employee->user;

    $this->travelTo(onMonday('22:00')->addHours(2)->addMinutes(30));

    $this->actingAs($user)->get('/driver')
        ->assertInertia(fn (Assert $page) => $page
            ->where('attendance.day', 'Monday')
            ->where('attendance.is_work_day', true)
            ->where('attendance.shift.ends_next_day', true)
            ->where('attendance.blocked_reason', null));

    $this->actingAs($user)->post('/driver/attendance/clock-in', shopLocation())->assertSessionHas('toast.type', 'success');

    expect(Attendance::where('employee_id', $rider->employee_id)->sole()->date->toDateString())->toBe('2026-11-02');
});

test('a day shift still works the same way', function () {
    $rider = onlineRider(driverShop(), scheduled: false);
    nightShift($rider->employee, 'Monday', '08:00', '17:00');

    $this->travelTo(onMonday('08:05'));
    $this->actingAs($rider->employee->user)->post('/driver/attendance/clock-in', shopLocation())->assertSessionHas('toast.type', 'success');

    expect(Attendance::where('employee_id', $rider->employee_id)->sole()->date->toDateString())->toBe('2026-11-02');
});
