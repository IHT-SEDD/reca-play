<?php

use App\Http\Controllers\TestingController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth')->group(function () {
 Route::post('/api/camera/start-recording', [TestingController::class, 'startRecording']);
 Route::post('/api/camera/stop-recording', [TestingController::class, 'stopRecording']);
});
