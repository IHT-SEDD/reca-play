<?php

use App\Http\Controllers\{
    LiveController,
    ProfileController,
    Auth\GoogleController,
    Recording\RecordingController,
    TestingController,
    Event\EventController,
    Field\FieldController,
};
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

    #region Masters
    Route::prefix('master')->middleware('role:super admin')->group(function () {});

    #region Profile
    Route::prefix('profile')->group(function () {
        Route::get('/edit', [ProfileController::class, 'edit'])->name('profile.edit');
        Route::patch('/update', [ProfileController::class, 'update'])->name('profile.update');
        Route::delete('/delete', [ProfileController::class, 'destroy'])->name('profile.destroy');
    });
});

require __DIR__ . '/auth.php';
