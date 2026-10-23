<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Confirms online payments whose customer never came back to the site. Needs the host's cron
// to run `php artisan schedule:run` every minute.
Schedule::command('payments:reconcile')
    ->everyFiveMinutes()
    ->withoutOverlapping(10);

// Applies each paid shop's time-based order rules (auto-complete, overdue reminders).
Schedule::command('orders:automate')
    ->everyMinute()
    ->withoutOverlapping(5);
