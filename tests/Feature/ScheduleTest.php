<?php

use Illuminate\Console\Scheduling\Event;
use Illuminate\Console\Scheduling\Schedule;

test('the one-minute cron runs the queue worker, and backups run nightly in the business time zone', function () {
    $tasks = collect(app(Schedule::class)->events())
        ->mapWithKeys(fn (Event $event) => [
            preg_replace('/^.*artisan[\'"]?\s+/', '', $event->command) => [$event->expression, (string) $event->timezone],
        ]);

    expect($tasks)
        ->toHaveKey('queue:work --stop-when-empty --max-time=50 --timeout=45')
        ->and($tasks['queue:work --stop-when-empty --max-time=50 --timeout=45'][0])->toBe('* * * * *')
        ->and($tasks['backup:run'])->toBe(['0 2 * * *', 'Asia/Kolkata'])
        ->and($tasks)->toHaveKeys(['backup:clean', 'backup:monitor', 'auth:clear-resets', 'queue:prune-failed --hours=720']);
});
