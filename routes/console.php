<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::command('app:expire-session-codes')
    ->dailyAt('00:05')
    ->withoutOverlapping()
    ->onSuccess(function () {
        Log::info('[Scheduler] ExpireSessionCodes ran successfully at ' . now());
    })
    ->onFailure(function () {
        Log::error('[Scheduler] ExpireSessionCodes failed at ' . now());
    });
