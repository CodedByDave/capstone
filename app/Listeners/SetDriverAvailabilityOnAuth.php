<?php

namespace App\Listeners;

use App\Models\User;
use App\Services\Driver\DriverAppService;
use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\Logout;
use Throwable;

/**
 * Drivers have no online switch: signing in puts them online (on a scheduled day) and signing out
 * takes them offline. A failure here is reported but never stops the sign-in or sign-out.
 */
class SetDriverAvailabilityOnAuth
{
    public function __construct(private readonly DriverAppService $drivers) {}

    public function onLogin(Login $event): void
    {
        $this->run($event->user, fn (User $user) => $this->drivers->goOnlineOnLogin($user));
    }

    public function onLogout(Logout $event): void
    {
        $this->run($event->user, fn (User $user) => $this->drivers->goOfflineOnLogout($user));
    }

    private function run(?object $user, callable $action): void
    {
        if (! $user instanceof User || $user->role !== 'staff') {
            return;
        }

        try {
            $action($user);
        } catch (Throwable $exception) {
            report($exception);
        }
    }
}
