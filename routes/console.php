<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Fire Bit reminders every minute (requires cron running `schedule:run`).
Schedule::command('bits:send-reminders')->everyMinute()->withoutOverlapping();
