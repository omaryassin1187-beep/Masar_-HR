<?php

use App\Console\Commands\CreateAutoDeductions;
use App\Console\Commands\CreateDailyAttendanceRecords;
use App\Console\Commands\DetermineAttendanceStatus;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::command(CreateDailyAttendanceRecords::class)->everyMinute();//dailyAt('10:05');
//Schedule::command(DetermineAttendanceStatus::class)->everyMinute();
//Schedule::command(CreateAutoDeductions::class)->everyMinute();//dailyAt('17:05');