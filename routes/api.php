<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\{AuthController, ProfileController, GameController};

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

Route::post('register', [AuthController::class, 'register']);
Route::post('login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('logout', [AuthController::class, 'logout']);

    Route::apiResource('profiles', ProfileController::class)->names([
        'index' => 'api.profiles.index',
        'store' => 'api.profiles.store',
        'show' => 'api.profiles.show',
        'update' => 'api.profiles.update',
        'destroy' => 'api.profiles.destroy',
    ]);
    Route::apiResource('games', GameController::class)->names([
        'index' => 'api.games.index',
        'store' => 'api.games.store',
        'show' => 'api.games.show',
        'update' => 'api.games.update',
        'destroy' => 'api.games.destroy',
    ]);
});
