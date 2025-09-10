<?php

use App\Http\Controllers\{
    ProfileController,
    TestingController,
    SupportingController,
};
use App\Http\Controllers\Auth\{
    GoogleController,
};
use App\Http\Controllers\Creator\CreatorController;
use App\Http\Controllers\Recording\{
    RecordingController,
};
use App\Http\Controllers\Event\{
    EventController,
};
use App\Http\Controllers\Field\{
    FieldController,
};
use App\Http\Controllers\Master\{
    MasterController,
    QrCode\QrCodeController
};
use App\Http\Controllers\Recording\QR\ScanQrController;
use App\Http\Controllers\UserManagement\{
    UserManagementController,
};
use Illuminate\Support\Facades\Route;

#region Home
Route::get('/', function () {
    return view('pages.home.index');
})->name('home.index');

// Guest Users
Route::middleware('guest')->group(function () {
    Route::get('auth/google', [GoogleController::class, 'redirectToGoogle'])->name('google.login');
    Route::get('auth/google/callback', [GoogleController::class, 'handleGoogleCallback']);
});

// Authenticated & Verified Users
Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/camera/live', [TestingController::class, 'livePreview'])->name('camera.live');
    Route::post('/api/camera/start-recording', [TestingController::class, 'start']);
    Route::post('/api/camera/stop-recording', [TestingController::class, 'stop']);

    #region Fields
    Route::prefix('field')->group(function () {
        Route::get('/', [FieldController::class, 'index'])->name('field.index');
    });

    #region Events
    Route::prefix('event')->group(function () {
        Route::get('/', [EventController::class, 'index'])->name('event.index');
    });

    #region My Recording
    Route::prefix('my-recording')->group(function () {
        Route::get('/', [RecordingController::class, 'index'])->name('recording.index');
    });

    #region Creator
    Route::prefix('creator')->group(function () {
        // Scan QR
        Route::prefix('scan-qr')->group(function () {
            Route::get('/', [CreatorController::class, 'scanQrPage'])->name('creator.scan');
            Route::post('/process', [CreatorController::class, 'scanQrProcess'])->name('creator.qr-process');
        });
        
        // Add new data
        Route::prefix('new')->group(function () {
            Route::get('/', [CreatorController::class, 'scanSuccessPage'])->name('creator.qr-success');
            Route::get('/check', [CreatorController::class, 'checkScannedQr'])->name('creator.qr-check');
            Route::post('/add/{mode}', [CreatorController::class, 'addNewData'])->name('creator.add-new');
        });

        // Record moment
        Route::prefix('record')->group(function () {
            Route::get('/', [CreatorController::class, 'recordPage'])->name('creator.record');
        });

        // Live stream
        Route::prefix('live-stream')->group(function () {
            Route::get('/', [CreatorController::class, 'liveStreamPage'])->name('creator.live-stream');
        });
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
        });

        #region Select Options
        Route::prefix('select')->group(function () {
            Route::get('/{option}', [SupportingController::class, 'selectOptions'])->name('select.options');
        });
    });

    #region Profile
    Route::prefix('profile')->group(function () {
        Route::get('/edit', [ProfileController::class, 'edit'])->name('profile.edit');
        Route::patch('/update', [ProfileController::class, 'update'])->name('profile.update');
        Route::delete('/delete', [ProfileController::class, 'destroy'])->name('profile.destroy');
    });
});

require __DIR__ . '/auth.php';
