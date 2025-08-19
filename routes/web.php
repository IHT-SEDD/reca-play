<?php

use App\Http\Controllers\LiveController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\TestingController;
use Illuminate\Support\Facades\Route;

#region Home
Route::get('/', function () {
    return view('pages.home.index');
})->name('home.index');

#region Guest
Route::middleware('guest')->group(function () {});

#region Authenticated
Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/camera/live', [TestingController::class, 'livePreview'])->name('camera.live');
    Route::post('/api/camera/start-recording', [TestingController::class, 'start']);
    Route::post('/api/camera/stop-recording', [TestingController::class, 'stop']);

    #region Fields
    Route::prefix('field')->group(function () {});

    #region Events
    Route::prefix('event')->group(function () {});

    #region Recordings
    Route::prefix('recording')->group(function () {});

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
