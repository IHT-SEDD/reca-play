<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\{
    Auth\GoogleController,
    Creator\CreatorController,
    Creator\RecordController,
    Creator\SelfieController,
    Creator\StreamController,
    Recording\RecordingController,
    Event\EventController,
    Master\MasterController,
    Master\QrCode\QrCodeController,
    UserManagement\UserManagementController,
    Venue\VenueController,
    VenueManagement\VenueManagementController,
    Home\HomeController,
    Watch\WatchController,
    MyProfile\MyProfileController,
    SuperAdmin\DownloadVideoController,
    SupportingController,
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
    Route::controller(HomeController::class)->group(function () {
        Route::get('/', 'index')->name('home.index');
        Route::get('/video-list', 'getVideos')->name('home.data');
    });

    Route::prefix('video/watch')->name('watch.')->controller(WatchController::class)->group(function () {
        Route::get('/{videoEncrypt}', 'index')->name('index');
        Route::get('/data/{videoEncrypt}', 'watchData')->name('data');
        Route::post('/like', 'likeVideo')->name('like-video');
        Route::post('/dislike', 'dislikeVideo')->name('dislike-video');
        Route::post('/follow', 'followOwnerVideo')->name('follow-owner');
    });

    /*
    |--------------------------------------------------------------------------
    | Venue Routes (Public)
    |--------------------------------------------------------------------------
    */
    Route::prefix('venue')->name('venue.')->controller(VenueController::class)->group(function () {
        Route::get('/', 'index')->name('index');
        Route::get('/data', 'data')->name('data');
    });


    /*
    |--------------------------------------------------------------------------
    | Event Routes (Public)
    |--------------------------------------------------------------------------
    */
    Route::prefix('event')->name('event.')->controller(EventController::class)->group(function () {
        Route::get('/', 'index')->name('index');
    });

    /*
    |--------------------------------------------------------------------------
    | Guest Routes
    |--------------------------------------------------------------------------
    */
    Route::middleware('guest')->group(function () {
        // Google Auth
        Route::prefix('auth/google')->name('google.')->controller(GoogleController::class)->group(function () {
            Route::get('/', 'redirectToGoogle')->name('login');
            Route::get('/callback', 'handleGoogleCallback')->name('callback');
        });
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

        /*
        |--------------------------------------------------------------------------
        | Venue Routes
        |--------------------------------------------------------------------------
        */
        Route::prefix('venue')->name('venue.')->controller(VenueController::class)->group(function () {
            Route::get('/detail/{hashedId}', 'detail')->name('detail');
            Route::get('/detail/data/{hashedId}', 'dataDetailPage')->name('detail-data');
            Route::get('/detail/field/{hashedId}', 'dataField')->name('field-data');
        });

        /*
        |--------------------------------------------------------------------------
        | My Recording
        |--------------------------------------------------------------------------
        */
        Route::prefix('my-recording')->name('recording.')->controller(RecordingController::class)->group(function () {
            Route::get('/', 'index')->name('index');
            Route::get('/recording-data', 'getRecordings')->name('data');
            Route::get('/watch/{hashedId}', 'getRecordings')->name('watch');
            Route::post('/get-video', 'getVideos')->name('get-video');
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
            Route::prefix('scan-qr')->name('creator.')->controller(CreatorController::class)->group(function () {
                Route::get('/', 'scanQrPage')->name('scan');

                Route::middleware('throttle:scan-qr')->group(function () {
                    Route::get('/process', 'scanQrProcess')->name('qr-process');
                });
            });

            /*
            |--------------------------------------------------
            | Add New Data
            |--------------------------------------------------
            */
            Route::prefix('new')->name('creator.')->controller(CreatorController::class)->group(function () {
                Route::get('/', 'scanSuccessPage')->name('qr-success');
                Route::get('/check', 'checkScannedQr')->name('qr-check');

                Route::middleware('throttle:add-data-creator')->group(function () {
                    Route::post('/add/{mode}', 'addNewData')->name('add-new');
                });
            });

            /*
            |--------------------------------------------------
            | Record Moment
            |--------------------------------------------------
            */
            Route::prefix('record')->name('creator.')->controller(RecordController::class)->group(function () {
                Route::get('/', 'recordPage')->name('record');
                Route::get('/check', 'checkData')->name('record-check');
                Route::get('/change-cam', 'changeCam')->name('record-change-cam');

                Route::middleware('throttle:stop-record')->group(function () {
                    Route::post('/stop', 'stopRecording')->name('record-stop');
                });
            });

            /*
            |--------------------------------------------------
            | Live Stream
            |--------------------------------------------------
            */
            Route::prefix('live-stream')->name('creator.')->controller(StreamController::class)->group(function () {
                Route::get('/', 'streamPage')->name('stream');
                Route::get('/check', 'checkData')->name('stream-check');

                Route::middleware('throttle:stop-stream')->group(function () {
                    Route::post('/stop', 'stopStreaming')->name('stream-stop');
                });
            });

            /*
            |--------------------------------------------------
            | Selfie
            |--------------------------------------------------
            */
            Route::prefix('selfie')->name('creator.')->controller(SelfieController::class)->group(function () {
                Route::get('/', 'selfiePage')->name('selfie');
                Route::get('/check', 'checkData')->name('selfie-check');

                Route::middleware('throttle:stop-selfie')->group(function () {
                    Route::post('/stop', 'stopSelfie')->name('selfie-stop');
                });
            });
        });

        /*
        |--------------------------------------------------------------------------
        | Profile Routes
        |--------------------------------------------------------------------------
        */
        Route::prefix('my-profile')->name('my-profile.')->controller(MyProfileController::class)->group(function () {
            Route::get('/', 'index')->name('index');
            Route::get('/user-data', 'userData')->name('user-data');
            Route::post('/{userId}/edit', 'editUser')->name('user-edit');
        });

        /*
        |--------------------------------------------------------------------------
        | Superadmin Routes
        |--------------------------------------------------------------------------
        */
        Route::middleware('role:superadmin')->group(function () {
            Route::prefix('master')->group(function () {
                // QR Code Download
                Route::prefix('qr_code')->name('qr_code.')->controller(QrCodeController::class)->group(function () {
                    Route::get('/download/{filename}', 'download')->name('download');
                });

                // Master Data
                Route::controller(MasterController::class)->name('master.')->group(function () {
                    Route::get('/{type}', 'index')->name('index');
                    Route::get('/{type}/data', 'datatable')->name('data');
                    Route::get('/{type}/{id}/edit', 'edit')->name('edit');
                    Route::post('/{type}/add-data', 'newData')->name('add-data');
                    Route::put('/{type}/update-data', 'updateData')->name('update-data');
                    Route::delete('/{type}/{id}/delete-data', 'deleteData')->name('delete-data');
                });
            });

            // User Management
            Route::prefix('user-management')->name('user-management.')->controller(UserManagementController::class)->group(function () {
                Route::get('/', 'index')->name('index');
                Route::get('/users-data', 'usersData')->name('data');
                Route::post('/add-data', 'addData')->name('add-data');
            });

            // Download Video
            Route::prefix('get-video')->name('get-video.')->controller(DownloadVideoController::class)->group(function () {
                Route::get('/', 'index')->name('index');
                Route::post('/add-data', 'addData')->name('add-data');
                Route::get('/file/{filename}', 'downloadFile')->name('file');
                Route::get('/status/{jobId}', 'status')->name('status');
                Route::post('/search', 'searchVideo')->name('search');
            });
        });

        /*
        |--------------------------------------------------------------------------
        | Owner Routes
        |--------------------------------------------------------------------------
        */
        Route::middleware('role:owner|cashier')->prefix('venue-management')->name('venue-management.')->controller(VenueManagementController::class)->group(function () {
            // --- Main pages ---
            Route::get('/', 'index')->name('index');
            Route::get('/field-data', 'fieldList')->name('field-list');
            Route::get('/data', 'data')->name('statistic-data');

            // --- Detail section ---
            Route::prefix('detail')->name('detail.')->group(function () {
                Route::get('/{hashedId}', 'detailFieldPage')->name('index');
                Route::get('/data/{hashedId}', 'detailFieldData')->name('data');
                Route::get('/last-activity/data/{hashedId}', 'lastActivity')->name('last-activity');
                Route::get('/access-code/data/{hashedId}', 'accessCode')->name('access-code');
                Route::post('/access-code/add/{hashedId}', 'newAccessCode')->name('generate-access-code');
                Route::post('/status/update/{hashedId}', 'updateStatusActive')->name('update-status');

                // --- Handle section ---
                Route::prefix('handle')->name('handle.')->group(function () {
                    Route::post('/start-record/{hashedId}', 'startRecordingOrStreaming')->name('start');
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
