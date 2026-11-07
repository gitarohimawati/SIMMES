<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Pengingat harian jadwal maintenance (H-7, H-1, Hari-H, dan Keterlambatan)
Schedule::command('simmes:reminders')->dailyAt('07:00');
Schedule::command('simmes:reminders')->hourly();

