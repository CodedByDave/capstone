<?php

use App\Notifications\DriverAssignmentNotification;
use Illuminate\Support\Facades\Notification;
use NotificationChannels\WebPush\WebPushChannel;

function pushPayload(array $overrides = []): array
{
    return array_merge([
        'endpoint' => 'https://fcm.googleapis.com/fcm/send/abc123',
        'keys' => ['p256dh' => 'BKey-public', 'auth' => 'auth-secret'],
    ], $overrides);
}

test('a driver registers their phone for job alerts', function () {
    $rider = onlineRider(driverShop());
    $user = $rider->employee->user;

    $this->actingAs($user)->postJson('/driver/push/subscribe', pushPayload())->assertOk();
    $this->actingAs($user)->postJson('/driver/push/subscribe', pushPayload())->assertOk();

    expect($user->pushSubscriptions()->count())->toBe(1);
});

test('a driver can remove a phone from job alerts', function () {
    $rider = onlineRider(driverShop());
    $user = $rider->employee->user;
    $this->actingAs($user)->postJson('/driver/push/subscribe', pushPayload());

    $this->actingAs($user)->postJson('/driver/push/unsubscribe', ['endpoint' => pushPayload()['endpoint']])->assertOk();

    expect($user->pushSubscriptions()->count())->toBe(0);
});

test('an insecure or incomplete subscription is rejected', function () {
    $user = onlineRider(driverShop())->employee->user;

    $this->actingAs($user)->postJson('/driver/push/subscribe', pushPayload(['endpoint' => 'http://insecure.test/x']))->assertUnprocessable();
    $this->actingAs($user)->postJson('/driver/push/subscribe', ['endpoint' => 'https://x.test/y'])->assertUnprocessable();
});

test('a non-driver cannot register for driver alerts', function () {
    $this->postJson('/driver/push/subscribe', pushPayload())->assertUnauthorized();
});

test('a new job assignment is sent as a web push as well as saved', function () {
    $notification = new DriverAssignmentNotification(waitingRun(driverShop()));
    $user = onlineRider(driverShop())->employee->user;

    expect($notification->via($user))->toContain('database', WebPushChannel::class);

    $message = $notification->toWebPush($user)->toArray();
    expect($message['title'])->toBe('New order assigned to you')
        ->and($message['data']['url'])->toStartWith('/driver/jobs/');
});
