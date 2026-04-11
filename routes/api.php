<?php

use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Auth\FrontierAuthController;
use App\Http\Controllers\Auth\FrontierCApiController;
use App\Http\Controllers\GalnetNewsController;
use App\Http\Controllers\MarketController;
use App\Http\Controllers\StationController;
use App\Http\Controllers\StatisticsController;
use App\Http\Controllers\SystemBodyController;
use App\Http\Controllers\SystemController;
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

Route::prefix('auth')->group(function () {
    Route::get('login', function () {
        return response()->json(['message' => 'Unauthorized.'], 401);
    })->name('login');

    Route::post('register', [AuthController::class, 'register']);
    Route::post('login', [AuthController::class, 'login']);

    Route::prefix('frontier')->group(function () {
        Route::get('login', [FrontierAuthController::class, 'login'])->name('frontier.auth.login');
        Route::get('callback', [FrontierAuthController::class, 'callback'])->name('frontier.auth.callback');
        Route::post('me', [FrontierAuthController::class, 'me'])->name('frontier.auth.me');
    });

    Route::middleware('auth:sanctum')->group(function () {
        Route::get('me', [AuthController::class, 'me']);
        Route::post('logout', [AuthController::class, 'logout']);
    });
});

Route::middleware('auth:sanctum')->prefix('frontier')->group(function () {
    Route::prefix('capi')->group(function () {
        Route::get('profile', [FrontierCApiController::class, 'profile']);
        Route::get('journal', [FrontierCApiController::class, 'journal']);
    });
});

Route::resource('systems', SystemController::class);

Route::prefix('system')->group(function () {
    Route::get('last-updated', [SystemController::class, 'getLastUpdated']);

    Route::prefix('search')->group(function () {
        Route::get('distance', [SystemController::class, 'searchByDistance']);
        Route::get('information', [SystemController::class, 'searchByInformation']);
        Route::get('route', [SystemController::class, 'searchRoute']);
    });

    Route::get('id64s', [SystemController::class, 'getId64s']);
});

Route::resource('bodies', SystemBodyController::class);

Route::resource('stations', StationController::class);

Route::prefix('station')->group(function () {
    Route::get('{slug}/market', [MarketController::class, 'getMarketDataForStation']);
});

Route::get('statistics', [StatisticsController::class, 'index']);

Route::prefix('galnet')->group(function () {
    Route::resource('news', GalnetNewsController::class);
});
