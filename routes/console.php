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
        Log::channel('expired-session')->info('[Scheduler] ExpireSessionCodes ran successfully at ' . now());
    })
    ->onFailure(function () {
        Log::channel('expired-session')->error('[Scheduler] ExpireSessionCodes failed at ' . now());
    });

Schedule::command('recording:auto-stop')->everyTwoMinutes();

Artisan::command('logs:clear-camera', function () {
    $logFiles = [
        'storage/logs/camera/control.log',
        'storage/logs/camera/record.log',
        'storage/logs/camera/stream.log',
        'storage/logs/camera/job.log',
    ];

    foreach ($logFiles as $file) {
        if (file_exists(base_path($file))) {
            file_put_contents(base_path($file), '');
            $this->info("Cleared: {$file}");
        } else {
            $this->warn("File not found: {$file}");
        }
    }

    $this->info('All specified log files have been cleared.');
})->describe('Clear specific camera log files');

Artisan::command('logs:clear-creator', function () {
    $logFiles = [
        'storage/logs/creator/creator.log',
    ];

    foreach ($logFiles as $file) {
        if (file_exists(base_path($file))) {
            file_put_contents(base_path($file), '');
            $this->info("Cleared: {$file}");
        } else {
            $this->warn("File not found: {$file}");
        }
    }

    $this->info('All specified log files have been cleared.');
})->describe('Clear specific creator log files');

Artisan::command('logs:clear', function () {
    $logFiles = [
        'storage/logs/laravel.log',
        'storage/logs/utility-service.log',
    ];

    foreach ($logFiles as $file) {
        if (file_exists(base_path($file))) {
            file_put_contents(base_path($file), '');
            $this->info("Cleared: {$file}");
        } else {
            $this->warn("File not found: {$file}");
        }
    }

    $this->info('All specified log files have been cleared.');
})->describe('Clear specific log files');
