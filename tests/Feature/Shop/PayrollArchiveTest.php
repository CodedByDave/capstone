<?php

// Uses the payShop helper declared in OrderPaymentTest.php and urlPayroll from PublicUrlIdentifierTest.php.

use App\Http\Middleware\EnforceOwnerPlatformPermissions;
use App\Http\Middleware\EnsureCurrentBusinessAgreementAccepted;
use App\Models\Payroll;
use App\Models\PayrollItem;

beforeEach(function () {
    $this->withoutMiddleware([
        EnsureCurrentBusinessAgreementAccepted::class,
        EnforceOwnerPlatformPermissions::class,
    ]);
});

test('archiving a payroll keeps it and its pay lines, and the archive lists it', function () {
    $shop = payShop();
    $payroll = urlPayroll($shop);
    $employee = urlEmployee($shop, 'EMP-ARC');
    PayrollItem::create([
        'payroll_id' => $payroll->id, 'employee_id' => $employee->id, 'days_worked' => 10,
        'basic_pay' => 5000, 'gross_pay' => 5000, 'total_deductions' => 0, 'net_pay' => 5000,
    ]);

    $this->actingAs($shop->owner)->delete("/shop/payroll/{$payroll->public_id}")->assertRedirect('/shop/payroll');

    expect(Payroll::find($payroll->id))->toBeNull()
        ->and(Payroll::onlyTrashed()->find($payroll->id))->not->toBeNull()
        ->and(PayrollItem::where('payroll_id', $payroll->id)->count())->toBe(1);

    $this->actingAs($shop->owner)->get('/shop/payroll?archived=1')
        ->assertInertia(fn ($page) => $page
            ->where('showArchived', true)
            ->where('payrolls.data.0.public_id', $payroll->public_id));
    $this->actingAs($shop->owner)->get('/shop/payroll')
        ->assertInertia(fn ($page) => $page->where('payrolls.total', 0));
});

test('an archived payroll can be restored', function () {
    $shop = payShop();
    $payroll = urlPayroll($shop);
    $payroll->delete();

    $this->actingAs($shop->owner)->post("/shop/payroll/{$payroll->public_id}/restore")->assertSessionHasNoErrors();

    expect(Payroll::find($payroll->id))->not->toBeNull();
});

test('another shop\'s archived payroll cannot be restored', function () {
    $shop = payShop();
    $other = payShop('Other Laundry');
    $foreign = urlPayroll($other);
    $foreign->delete();

    $this->actingAs($shop->owner)->post("/shop/payroll/{$foreign->public_id}/restore")->assertNotFound();

    expect(Payroll::find($foreign->id))->toBeNull();
});

test('a period held by an archived payroll asks the shop to restore it', function () {
    $shop = payShop();
    $payroll = urlPayroll($shop);
    $payroll->delete();

    $this->actingAs($shop->owner)->post('/shop/payroll', [
        'period_label' => 'October 2026',
        'period_start' => '2026-10-01',
        'period_end' => '2026-10-31',
    ])->assertSessionHasErrors(['period_start' => 'An archived payroll already covers this period. Restore it from the archive instead.']);

    expect(Payroll::withTrashed()->count())->toBe(1);
});
