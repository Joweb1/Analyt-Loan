<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

use Illuminate\Support\Facades\Schedule;

Schedule::command('app:midnight-sync')->dailyAt('00:00');
Schedule::command('app:send-daily-report')->dailyAt('20:00');
Schedule::command('loans:overdue-reminders')->dailyAt('08:00');
