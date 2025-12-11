<?php

use App\Http\Controllers\Api\Button\HighlightController;
use App\Http\Controllers\Api\SearchVideoController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API SEARCH CAMERA ROUTES
|--------------------------------------------------------------------------
*/

Route::post('/camera/search', [SearchVideoController::class, 'search']);

/*
|--------------------------------------------------------------------------
| API HIGHLIGHT BUTTON ROUTES
|--------------------------------------------------------------------------
*/
Route::prefix('highlight/button')->name('highlight.button.')->controller(HighlightController::class)->group(function () {
 Route::post('/get-data', 'getData')->name('get-data');
 Route::post('/send-data', 'sendData')->name('send-data');
});
