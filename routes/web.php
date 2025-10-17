<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\{
    ProfileController,
    TestingController,
    SupportingController,
    Auth\GoogleController,
    Creator\CreatorController,
    Creator\RecordController,
    Recording\RecordingController,
    Event\EventController,
    Master\MasterController,
    Master\QrCode\QrCodeController,
    UserManagement\UserManagementController,
    Venue\VenueController,
    VenueManagement\VenueManagementController,
    Home\HomeController,
    Watch\WatchController,
    ScanQrController
};

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

Route::middleware(['check.maintenance'])->group(function () {

    /*
    |--------------------------------------------------------------------------
    | Home & Public Pages
    |--------------------------------------------------------------------------
    */
    Route::get('/', [HomeController::class, 'index'])->name('home.index');
    Route::get('/video-list', [HomeController::class, 'getVideos'])->name('home.data');
    Route::get('/video/watch/{videoEncrypt}', [WatchController::class, 'index'])->name('watch.index');
    Route::get('/video/watch/data/{videoEncrypt}', [WatchController::class, 'watchData'])->name('watch.data');

    /*
    |--------------------------------------------------------------------------
    | Guest Routes
    |--------------------------------------------------------------------------
    */
    Route::middleware('guest')->group(function () {
        // Google Auth
        Route::get('auth/google', [GoogleController::class, 'redirectToGoogle'])->name('google.login');
        Route::get('auth/google/callback', [GoogleController::class, 'handleGoogleCallback']);
    });

    /*
    |--------------------------------------------------------------------------
    | Scan QR (Guest & Auth)
    |--------------------------------------------------------------------------
    */
    Route::get('scan-qr/{token}', [ScanQrController::class, 'index'])->name('scan-qr');

    /*
    |--------------------------------------------------------------------------
    | Authenticated Routes
    |--------------------------------------------------------------------------
    */
    Route::middleware(['auth', 'check.creator.session'])->group(function () {
        // Share video (with rate limit)
        Route::middleware('throttle:share-video')->group(function () {
            Route::post('/share/{videoId}', [SupportingController::class, 'shareVideo'])->name('support.share');
        });

        // Download video (with rate limit)
        Route::middleware('throttle:download-video')->group(function () {
            Route::post('/download/{videoId}', [SupportingController::class, 'downloadVideo'])->name('support.download');
        });

        // Camera Testing
        Route::get('/camera/live', [TestingController::class, 'livePreview'])->name('camera.live');
        Route::post('/api/camera/start-recording', [TestingController::class, 'start']);
        Route::post('/api/camera/stop-recording', [TestingController::class, 'stop']);

        /*
        |--------------------------------------------------------------------------
        | Venue Routes
        |--------------------------------------------------------------------------
        */
        Route::prefix('venue')->group(function () {
            Route::get('/', [VenueController::class, 'index'])->name('venue.index');
            Route::get('/data', [VenueController::class, 'data'])->name('venue.data');
            Route::get('/detail/{hashedId}', [VenueController::class, 'detail'])->name('venue.detail');
            Route::get('/detail/data/{hashedId}', [VenueController::class, 'dataDetailPage'])->name('venue.detail-data');
            Route::get('/detail/field/{hashedId}', [VenueController::class, 'dataField'])->name('venue.field-data');
        });

        /*
        |--------------------------------------------------------------------------
        | Event Routes
        |--------------------------------------------------------------------------
        */
        Route::prefix('event')->group(function () {
            Route::get('/', [EventController::class, 'index'])->name('event.index');
        });

        /*
        |--------------------------------------------------------------------------
        | My Recording
        |--------------------------------------------------------------------------
        */
        Route::prefix('my-recording')->group(function () {
            Route::get('/', [RecordingController::class, 'index'])->name('recording.index');
            Route::get('/recording-data', [RecordingController::class, 'getRecordings'])->name('recording.data');
            Route::get('/watch/{hashedId}', [RecordingController::class, 'getRecordings'])->name('recording.watch');
        });

        /*
        |--------------------------------------------------------------------------
        | Creator Routes
        |--------------------------------------------------------------------------
        */
        Route::prefix('creator')->group(function () {
            // Redirect Page
            Route::get('/redirect', fn() => view('pages.creator.redirect'))->name('creator.redirect');

            /*
            |--------------------------------------------------
            | Scan QR
            |--------------------------------------------------
            */
            Route::prefix('scan-qr')->group(function () {
                Route::get('/', [CreatorController::class, 'scanQrPage'])->name('creator.scan');

                Route::middleware('throttle:scan-qr')->group(function () {
                    Route::post('/process', [CreatorController::class, 'scanQrProcess'])->name('creator.qr-process');
                });
            });

            /*
            |--------------------------------------------------
            | Add New Data
            |--------------------------------------------------
            */
            Route::prefix('new')->group(function () {
                Route::get('/', [CreatorController::class, 'scanSuccessPage'])->name('creator.qr-success');
                Route::get('/check', [CreatorController::class, 'checkScannedQr'])->name('creator.qr-check');

                Route::middleware('throttle:add-data-creator')->group(function () {
                    Route::post('/add/{mode}', [CreatorController::class, 'addNewData'])->name('creator.add-new');
                });
            });

            /*
            |--------------------------------------------------
            | Record Moment
            |--------------------------------------------------
            */
            Route::prefix('record')->group(function () {
                Route::get('/', [RecordController::class, 'recordPage'])->name('creator.record');
                Route::get('/check', [RecordController::class, 'checkData'])->name('creator.record-check');

                Route::middleware('throttle:stop-record')->group(function () {
                    Route::post('/stop', [RecordController::class, 'stopRecording'])->name('creator.record-stop');
                });
            });

            /*
            |--------------------------------------------------
            | Live Stream
            |--------------------------------------------------
            */
            Route::prefix('live-stream')->group(function () {
                Route::get('/', [CreatorController::class, 'liveStreamPage'])->name('creator.live-stream');
            });
        });

        /*
        |--------------------------------------------------------------------------
        | Profile Routes
        |--------------------------------------------------------------------------
        */
        Route::prefix('profile')->group(function () {
            Route::get('/', [ProfileController::class, 'index'])->name('profile.index');
            Route::get('/edit', [ProfileController::class, 'edit'])->name('profile.edit');
            Route::patch('/update', [ProfileController::class, 'update'])->name('profile.update');
            Route::delete('/delete', [ProfileController::class, 'destroy'])->name('profile.destroy');
        });

        /*
        |--------------------------------------------------------------------------
        | Superadmin Routes
        |--------------------------------------------------------------------------
        */
        Route::middleware('role:superadmin')->group(function () {
            // Master Data
            Route::prefix('master')->group(function () {
                Route::get('/{type}', [MasterController::class, 'index'])->name('master.index');
                Route::get('/{type}/data', [MasterController::class, 'datatable'])->name('master.data');
                Route::post('/{type}/add-data', [MasterController::class, 'newData'])->name('master.add-data');

                // QR Code Download
                Route::prefix('qr_code')->group(function () {
                    Route::get('/download/{filename}', [QrCodeController::class, 'download'])->name('qr_code.download');
                });
            });

            // User Management
            Route::prefix('user-management')->group(function () {
                Route::get('/', [UserManagementController::class, 'index'])->name('user-management.index');
                Route::get('/users-data', [UserManagementController::class, 'usersData'])->name('user-management.data');
                Route::post('/add-data', [UserManagementController::class, 'addData'])->name('user-management.add-data');
            });
        });

        /*
        |--------------------------------------------------------------------------
        | Owner Routes
        |--------------------------------------------------------------------------
        */
        Route::middleware('role:owner|cashier')->group(function () {
            Route::prefix('venue-management')->group(function () {
                Route::get('/', [VenueManagementController::class, 'index'])->name('venue-management.index');
                Route::get('/field-data', [VenueManagementController::class, 'fieldList'])->name('venue-management.field-list');
                Route::get('/data', [VenueManagementController::class, 'data'])->name('venue-management.statistic-data');

                Route::prefix('detail')->group(function () {
                    Route::get('/{hashedId}', [VenueManagementController::class, 'detailFieldPage'])->name('venue-management.detail-field');
                    Route::get('/data/{hashedId}', [VenueManagementController::class, 'detailFieldData'])->name('venue-management.detail-field-data');
                    Route::get('/last-activity/data/{hashedId}', [VenueManagementController::class, 'lastActivity'])->name('venue-management.last-activity');
                    Route::post('/status/update/{hashedId}', [VenueManagementController::class, 'updateStatusActive'])->name('venue-management.update-status');
                    Route::post('/code-access/generate/{hashedId}', [VenueManagementController::class, 'generateAccessCode'])->name('venue-management.generate-access-code');
                });
            });
        });

        /*
        |--------------------------------------------------------------------------
        | Select Options (Global)
        |--------------------------------------------------------------------------
        */
        Route::prefix('select')->group(function () {
            Route::get('/{option}', [SupportingController::class, 'selectOptions'])->name('select.options');
        });
    });
});

require __DIR__ . '/auth.php';
