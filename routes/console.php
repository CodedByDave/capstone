<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Log;
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

// Backups of the database and uploaded files. A weekly set every Sunday and a monthly set on the 1st;
// see config/backup.php for the destination disk and how many sets are kept.
Schedule::command('backup:run --type=weekly')
    ->weeklyOn(0, '02:00')
    ->withoutOverlapping(60)
    ->onFailure(fn () => Log::critical('Weekly backup failed'));

Schedule::command('backup:run --type=monthly')
    ->monthlyOn(1, '03:00')
    ->withoutOverlapping(60)
    ->onFailure(fn () => Log::critical('Monthly backup failed'));
