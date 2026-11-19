<?php

use App\Http\Middleware\EnforceOwnerPlatformPermissions;
use App\Http\Middleware\EnsureCurrentBusinessAgreementAccepted;
use Illuminate\Notifications\DatabaseNotification;
use Illuminate\Support\Str;

beforeEach(function () {
    $this->withoutMiddleware([
        EnsureCurrentBusinessAgreementAccepted::class,
        EnforceOwnerPlatformPermissions::class,
    ]);
});

function bellNotification($user, string $title = 'Order approved'): DatabaseNotification
{
    return DatabaseNotification::create([
        'id' => (string) Str::uuid(),
        'type' => 'App\Notifications\Test',
        'notifiable_type' => $user::class,
        'notifiable_id' => $user->id,
        'data' => ['type' => 'order_approved', 'title' => $title, 'body' => 'Body', 'url' => '/shop/dashboard'],
    ]);
}

test('the bell lists the latest notifications without marking them read', function () {
    $owner = driverShop()->owner;
    bellNotification($owner, 'First');
    bellNotification($owner, 'Second');

    $this->actingAs($owner)
        ->getJson('/shop/notifications/latest')
        ->assertOk()
        ->assertJsonCount(2, 'notifications')
        ->assertJsonPath('unread', 2);

    expect($owner->unreadNotifications()->count())->toBe(2);
});

test('the bell shows at most eight notifications', function () {
    $owner = driverShop()->owner;
    foreach (range(1, 10) as $i) {
        bellNotification($owner, "N{$i}");
    }

    $this->actingAs($owner)
        ->getJson('/shop/notifications/latest')
        ->assertJsonCount(8, 'notifications')
        ->assertJsonPath('unread', 10);
});

test('clicking a notification marks only that one read', function () {
    $owner = driverShop()->owner;
    $first = bellNotification($owner);
    bellNotification($owner);

    $this->actingAs($owner)
        ->postJson("/shop/notifications/{$first->id}/read")
        ->assertOk()
        ->assertJsonPath('unread', 1);

    expect($first->refresh()->read_at)->not->toBeNull();
});

test('a user cannot mark another user\'s notification read', function () {
    $owner = driverShop()->owner;
    $other = driverShop()->owner;
    $theirs = bellNotification($other);

    $this->actingAs($owner)->postJson("/shop/notifications/{$theirs->id}/read")->assertOk();

    expect($theirs->refresh()->read_at)->toBeNull();
});
