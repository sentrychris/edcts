<?php

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


    Route::prefix('frontier')->group(function() {
        Route::get('login', [\App\Http\Controllers\Auth\FrontierAuthController::class, 'login'])->name('frontier.auth.login');
        Route::get('callback', [\App\Http\Controllers\Auth\FrontierAuthController::class, 'callback'])->name('frontier.auth.callback');
        Route::post('me', [\App\Http\Controllers\Auth\FrontierAuthController::class, 'me'])->name('frontier.auth.me');
    });

    Route::middleware('auth:sanctum')->group(function() {
        Route::get('me', [\App\Http\Controllers\Auth\AuthController::class, 'me']);
        Route::post('logout', [\App\Http\Controllers\Auth\AuthController::class, 'logout']);
    });
});

Route::middleware('auth:sanctum')->prefix('frontier')->group(function() {
    Route::prefix('capi')->group(function() {
        Route::get('profile', [\App\Http\Controllers\Auth\FrontierCApiController::class, 'profile']);
        Route::get('journal', [\App\Http\Controllers\Auth\FrontierCApiController::class, 'journal']);
    });
});

Route::resource('systems', App\Http\Controllers\SystemController::class);

Route::prefix('system')->group(function() {
    Route::get('last-updated', [\App\Http\Controllers\SystemController::class, 'getLastUpdated']);
    Route::prefix('search')->group(function() {
        Route::get('distance', [App\Http\Controllers\SystemController::class, 'searchByDistance']);
        Route::get('information', [App\Http\Controllers\SystemController::class, 'searchByInformation']);
    });
    Route::get('id64', [\App\Http\Controllers\SystemController::class, 'listId64s']);
});

Route::resource('bodies', App\Http\Controllers\SystemBodyController::class);

Route::resource('stations', App\Http\Controllers\StationController::class);
Route::prefix('station')->group(function() {
    Route::get('{slug}/market', [App\Http\Controllers\MarketController::class, 'getMarketDataForStation']);
});

Route::get('statistics', [App\Http\Controllers\StatisticsController::class, 'getStatistics']);

Route::prefix('galnet')->group(function() {
    Route::resource('news', App\Http\Controllers\GalnetNewsController::class);
});