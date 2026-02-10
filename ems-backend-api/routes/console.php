<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;
use App\Models\SystemSetting;
use Carbon\Carbon;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Schedule Monthly Fee Generation
Schedule::command('fees:generate')->monthlyOn(1, '01:00');



// Schedule Auto-Drop Unpaid Students - run daily to check logic
// Schedule Auto-Drop Unpaid Students - run daily to check logic
Schedule::command('enrollments:auto-drop-unpaid')->dailyAt('02:00');

// Schedule Monthly Attendance Check - Last day of month at 23:00
Schedule::command('attendance:check-monthly')->lastDayOfMonth('23:00');
