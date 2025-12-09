<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schedule;

/*
|--------------------------------------------------------------------------
| Basic Artisan Commands
|--------------------------------------------------------------------------
*/

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

/*
|--------------------------------------------------------------------------
| Scheduled Commands
|--------------------------------------------------------------------------
*/

// Update status expired session codes daily at 00:05
Schedule::command('app:expire-session-codes')
    ->dailyAt('00:05')
    ->withoutOverlapping()
    ->onSuccess(function () {
        Log::channel('expired-session')->info('[Scheduler] ExpireSessionCodes ran successfully at ' . now());
    })
    ->onFailure(function () {
        Log::channel('expired-session')->error('[Scheduler] ExpireSessionCodes failed at ' . now());
    });

// Auto stop recordings every two minutes
Schedule::command('recording:auto-stop')->everyTwoMinutes();

// Clean orphan sessions every three minutes
Schedule::command('sessions:clean-orphan')
    ->everyThreeMinutes()
    ->withoutOverlapping()
    ->onSuccess(function () {
        Log::channel('orphan-session')->info('[Scheduler] OrphanSession ran successfully at ' . now());
    })
    ->onFailure(function () {
        Log::channel('orphan-session')->error('[Scheduler] OrphanSession failed at ' . now());
    });

// Check recordings videos every two hours
Schedule::command('recordings:check-videos')->everyTwoHours();

// Clean temporary recordings every two days at 01:00
Schedule::command('temp-recordings:clean')->days(2)->at('01:00');

/*
|--------------------------------------------------------------------------
| Log Clearing Artisan Commands
|--------------------------------------------------------------------------
*/

// Clear specific camera log files
Artisan::command('logs:clear-camera', function () {
    $logFiles = [
        'storage/logs/camera/control.log',
        'storage/logs/camera/record.log',
        'storage/logs/camera/stream.log',
        'storage/logs/camera/job.log',
        'storage/logs/camera/auto-stop-cron.log',
        'storage/logs/worker/camera_download.log',
        'storage/logs/worker/camera_record_process.log',
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

// Clear specific creator log files
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

// Clear specific general log files
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

// Clear specific session log files
Artisan::command('logs:clear-session', function () {
    $logFiles = [
        'storage/logs/session/expired-session.log',
        'storage/logs/session/orphan-session.log',
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
})->describe('Clear specific session log files');
