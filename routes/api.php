<?php

use App\Http\Controllers\Api\Auth\AuthController;
use App\Http\Controllers\Api\V1\GifController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
 */

Route::prefix('auth')->middleware('service.log')->group(function () {
    Route::post('login', [AuthController::class, 'login']);
});

Route::prefix('v1')->middleware('service.log')->group(function () {
    Route::apiResource('gifs', GifController::class)
         ->except(['update', 'destroy'])
         ->middleware('auth:api');
});
