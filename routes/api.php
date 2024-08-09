<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\GameController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;



Route::post('/login', [AuthController::class, 'login']);
Route::post('/register', [AuthController::class, 'register']);
Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');



Route::group(['middleware' => 'auth:sanctum'] , function () {
    Route::post('/game/reserve', [GameController::class, 'reserve']);
    Route::post('/game/change', [GameController::class, 'change']);
});


Route::get('/games', [GameController::class, 'index']);
Route::get('/games/{id}', [GameController::class, 'single']);

// delete
Route::get('/create-games', [GameController::class, 'cron']);


Route::group(['middleware' => 'auth:sanctum'], function(){
    Route::post('user', [AuthController::class, 'user']);
});
