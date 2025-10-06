<?php

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
    VenueManagement\VenueManagementController
};
use App\Http\Controllers\Home\HomeController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::middleware(['check.maintenance'])->group(function () {
    #region Home
    Route::get('/', [HomeController::class, 'index'])->name('home.index');
    Route::get('/video-list', [HomeController::class, 'getVideos'])->name('home.data');

    // Guest Users
    Route::middleware('guest')->group(function () {
        #region Google Auth
        Route::get('auth/google', [GoogleController::class, 'redirectToGoogle'])->name('google.login');
        Route::get('auth/google/callback', [GoogleController::class, 'handleGoogleCallback']);
    });

    // Authenticated & Verified Users
    Route::middleware(['auth', 'verified'])->group(function () {
        // Share video & watch
        Route::middleware('throttle:share-video')->group(function () {
            Route::post('/share/{videoId}', [HomeController::class, 'shareVideo'])->name('home.share');
            Route::get('/video/watch/{videoEncrypt}', [HomeController::class, 'watchVideo'])->name('home.watch');
        });

        Route::get('/camera/live', [TestingController::class, 'livePreview'])->name('camera.live');
        Route::post('/api/camera/start-recording', [TestingController::class, 'start']);
        Route::post('/api/camera/stop-recording', [TestingController::class, 'stop']);

        #region Venues
        Route::prefix('venue')->group(function () {
            Route::get('/', [VenueController::class, 'index'])->name('venue.index');
            Route::get('/data', [VenueController::class, 'data'])->name('venue.data');
            Route::get('/detail/{hashedId}', [VenueController::class, 'detail'])->name('venue.detail');
            Route::get('/detail/data/{hashedId}', [VenueController::class, 'dataDetailPage'])->name('venue.detail-data');
            Route::get('/detail/field/{hashedId}', [VenueController::class, 'dataField'])->name('venue.field-data');
        });

        #region Events
        Route::prefix('event')->group(function () {
            Route::get('/', [EventController::class, 'index'])->name('event.index');
        });

        #region My Recording
        Route::prefix('my-recording')->group(function () {
            Route::get('/', [RecordingController::class, 'index'])->name('recording.index');
            Route::get('/recording-data', [RecordingController::class, 'getRecordings'])->name('recording.data');
            Route::get('/watch/{hashedId}', [RecordingController::class, 'getRecordings'])->name('recording.watch');
        });

        #region Creator
        Route::prefix('creator')->group(function () {
            // Scan QR
            Route::prefix('scan-qr')->group(function () {
                Route::get('/', [CreatorController::class, 'scanQrPage'])->name('creator.scan');

                // Rate limiting for process scanning-qr
                Route::middleware('throttle:scan-qr')->group(function () {
                    Route::post('/process', [CreatorController::class, 'scanQrProcess'])->name('creator.qr-process');
                });
            });

            // Add new data
            Route::prefix('new')->group(function () {
                Route::get('/', [CreatorController::class, 'scanSuccessPage'])->name('creator.qr-success');
                Route::get('/check', [CreatorController::class, 'checkScannedQr'])->name('creator.qr-check');

                // Rate limiting for process add data creator
                Route::middleware('throttle:add-data-creator')->group(function () {
                    Route::post('/add/{mode}', [CreatorController::class, 'addNewData'])->name('creator.add-new');
                });
            });

            // Record moment
            Route::prefix('record')->group(function () {
                Route::get('/', [RecordController::class, 'recordPage'])->name('creator.record');
                Route::get('/check', [RecordController::class, 'checkData'])->name('creator.record-check');

                // Rate limiting for process stop recording
                Route::middleware('throttle:stop-record')->group(function () {
                    Route::post('/stop', [RecordController::class, 'stopRecording'])->name('creator.record-stop');
                });
            });

            // Live stream
            Route::prefix('live-stream')->group(function () {
                Route::get('/', [CreatorController::class, 'liveStreamPage'])->name('creator.live-stream');
            });
        });

        #region Profile
        Route::prefix('profile')->group(function () {
            Route::get('/edit', [ProfileController::class, 'edit'])->name('profile.edit');
            Route::patch('/update', [ProfileController::class, 'update'])->name('profile.update');
            Route::delete('/delete', [ProfileController::class, 'destroy'])->name('profile.destroy');
        });

        // Superadmin Routes
        Route::middleware('role:superadmin')->group(function () {
            #region Masters
            Route::prefix('master')->group(function () {
                Route::get('/{type}', [MasterController::class, 'index'])->name('master.index');
                Route::get('/{type}/data', [MasterController::class, 'datatable'])->name('master.data');
                Route::post('/{type}/add-data', [MasterController::class, 'newData'])->name('master.add-data');

                #region QR Download
                Route::prefix('qr_code')->group(function () {
                    Route::get('/download/{filename}', [QrCodeController::class, 'download'])->name('qr_code.download');
                });
            });

            #region User Management
            Route::prefix('user-management')->group(function () {
                Route::get('/', [UserManagementController::class, 'index'])->name('user-management.index');
                Route::get('/users-data', [UserManagementController::class, 'usersData'])->name('user-management.data');
                Route::post('/add-data', [UserManagementController::class, 'addData'])->name('user-management.add-data');
            });
        });

        // Owner Routes
        Route::middleware('role:owner')->group(function () {
            #region Venue Management
            Route::prefix('venue-management')->group(function () {
                Route::get('/', [VenueManagementController::class, 'index'])->name('venue-management.index');
                Route::get('/field-data', [VenueManagementController::class, 'fieldList'])->name('venue-management.field-list');
                Route::get('/data', [VenueManagementController::class, 'data'])->name('venue-management.statistic-data');

                Route::prefix('detail')->group(function () {
                    Route::get('/{hashedId}', [VenueManagementController::class, 'detailFieldPage'])->name('venue-management.detail-field');
                    Route::get('/data/{hashedId}', [VenueManagementController::class, 'detailFieldData'])->name('venue-management.detail-field-data');
                    Route::get('/last-activity/data/{hashedId}', [VenueManagementController::class, 'lastActivity'])->name('venue-management.last-activity');
                    Route::post('/status/update/{hashedId}', [VenueManagementController::class, 'updateStatusActive'])->name('venue-management.update-status');
                });
            });
        });

        #region Select Options
        Route::prefix('select')->group(function () {
            Route::get('/{option}', [SupportingController::class, 'selectOptions'])->name('select.options');
        });
    });
});

require __DIR__ . '/auth.php';
