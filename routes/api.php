<?php

use Illuminate\Http\Request;
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

Route::prefix('auth')->group(function() {
    Route::get('login', function () {
        return response()->json(['message' => 'Unauthorized.'], 401);
    })->name('login');
    
    Route::post('register', [\App\Http\Controllers\Auth\AuthController::class, 'register']);
    Route::post('login', [\App\Http\Controllers\Auth\AuthController::class, 'login']);

    Route::middleware('auth:sanctum')->group(function() {
        Route::get('me', [\App\Http\Controllers\Auth\AuthController::class, 'me']);
        Route::post('logout', [\App\Http\Controllers\Auth\AuthController::class, 'logout']);
    });
});

Route::prefix('fleet')->group(function() {
    Route::resource('carriers', App\Http\Controllers\FleetCarrierController::class);
    Route::resource('schedule', App\Http\Controllers\FleetScheduleController::class);
});

Route::prefix('galnet')->group(function() {
    Route::resource('news', App\Http\Controllers\GalnetNewsController::class);
});