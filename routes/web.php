<?php

use App\Http\Controllers\{
    LiveController,
    ProfileController,
    Auth\GoogleController,
    Recording\RecordingController,
    TestingController,
    Event\EventController,
    Field\FieldController,
    SupportingController,
};
use App\Http\Controllers\Master\MasterController;
use App\Http\Controllers\UserManagement\UserManagementController;
use Illuminate\Support\Facades\Route;

#region Home
Route::get('/', function () {
    return view('pages.home.index');
})->name('home.index');

#region Guest
Route::middleware('guest')->group(function () {
    Route::get('auth/google', [GoogleController::class, 'redirectToGoogle'])->name('google.login');
    Route::get('auth/google/callback', [GoogleController::class, 'handleGoogleCallback']);
});

#region Authenticated
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

    #region Recordings
    Route::prefix('recording')->group(function () {
        Route::get('/', [RecordingController::class, 'index'])->name('recording.index');
    });

    Route::middleware('role:superadmin')->group(function () {
        #region Masters
        Route::prefix('master')->group(function () {
            Route::get('/{type}', [MasterController::class, 'index'])->name('master.index');
            Route::get('/{type}/data', [MasterController::class, 'datatable'])->name('master.data');
            Route::post('/{type}/add-data', [MasterController::class, 'newData'])->name('master.add-data');
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
