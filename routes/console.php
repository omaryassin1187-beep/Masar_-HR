<?php

use App\Console\Commands\CompleteApprovedOverTimes;
use App\Console\Commands\CreateAutoDeductions;
use App\Console\Commands\CreateDailyAttendanceRecords;
use App\Console\Commands\DetermineAttendanceStatus;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::command(CreateDailyAttendanceRecords::class)->dailyAt('08:00');//;dailyAt('08:00')
Schedule::command(DetermineAttendanceStatus::class)->everyTenMinutes();//everyTenMinutes();
Schedule::command('offers:expire')->daily();
Schedule::command('contracts:notify-expiring')->daily();
Schedule::command('contracts:update-statuses')->daily();

Schedule::command('candidates:daily-report')->dailyAt('17:00');

Schedule::command('announcements:update-status')->everyTenMinutes();

Schedule::command(CreateAutoDeductions::class)->dailyAt('23:50');//dailyAt('23:50');
Schedule::command(CompleteApprovedOverTimes::class)->everyTenMinutes();//everyTenMinutes();
