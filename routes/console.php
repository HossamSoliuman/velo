<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

/*
|--------------------------------------------------------------------------
| Scheduled Tasks
|--------------------------------------------------------------------------
|
| The server runs one cron job every minute: `php artisan schedule:run`.
| Everything below, including the queue worker, is started from it, so
| no Supervisor or other long-running process is needed on the host.
|
*/

// Nightly backup of the database and uploads, in the business's time zone. Failures are emailed (config/backup.php).
Schedule::timezone(config('app.display_timezone'))->group(function () {
    Schedule::command('backup:clean')->dailyAt('01:30');
    Schedule::command('backup:run')->dailyAt('02:00')->withoutOverlapping(120);
    Schedule::command('backup:monitor')->dailyAt('09:00');
});

Schedule::command('auth:clear-resets')->daily();
Schedule::command('queue:prune-failed --hours=720')->daily();

// Sends queued emails. Stops once the queue is empty or after 50 seconds, before the next run starts.
Schedule::command('queue:work --stop-when-empty --max-time=50 --timeout=45')
    ->everyMinute()
    ->withoutOverlapping(5);
