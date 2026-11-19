<?php

use App\Models\ShopOrder;
use Illuminate\Support\Carbon;

function orderDue(string $status, ?Carbon $due, ?Carbon $startedAt = null): ShopOrder
{
    return (new ShopOrder)->forceFill([
        'status' => $status,
        'estimated_completion_at' => $due,
        'in_progress_at' => $startedAt,
        'created_at' => now()->subHours(5),
    ]);
}

test('an order still in the queue past its due time is overdue', function () {
    expect(orderDue('in_progress', now()->subMinute())->isOverdue())->toBeTrue()
        ->and(orderDue('pending', now()->subHour())->isOverdue())->toBeTrue();
});

test('an order not yet due is not overdue', function () {
    expect(orderDue('in_progress', now()->addHour())->isOverdue())->toBeFalse();
});

test('finished and cancelled orders are never overdue', function () {
    foreach (['completed', 'picked_up', 'cancelled'] as $status) {
        expect(orderDue($status, now()->subDay())->isOverdue())->toBeFalse();
    }
});

test('an order with no due time is not overdue', function () {
    expect(orderDue('in_progress', null)->isOverdue())->toBeFalse();
});
