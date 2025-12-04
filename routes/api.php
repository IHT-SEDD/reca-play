<?php

use App\Http\Controllers\Api\SearchVideoController;
use Illuminate\Support\Facades\Route;

Route::post('/camera/search', [SearchVideoController::class, 'search']);