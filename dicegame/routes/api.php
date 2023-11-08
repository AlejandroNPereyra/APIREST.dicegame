<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\api\UserController;

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

Route::post('/login', [UserController::class, 'login']);
Route::post('/logout', [UserController::class, 'logout'])->middleware('auth:api');
Route::post('/players', [UserController::class, 'register']);

Route::middleware('auth:api')->group(function() {

    Route::middleware('role:gamer')->group(function() {

        Route::put('/players/{id}', [UserController::class, 'updateAlias']);

    });

    Route::middleware('role:admin')->group(function() {

        Route::get('/players', [UserController::class, 'gamersIndex']);
        Route::get('players/ranking', [UserController::class, 'rankingIndex']);
        Route::get('players/ranking/winner', [UserController::class, 'highestRank']);

    });

});