<?php

use App\Console\Commands\GameCron;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote')->hourly();


Schedule::command(GameCron::class)->daily();

//Schedule::call(\App\Console\Commands\GameCron::class)->daily();
