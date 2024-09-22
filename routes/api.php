<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\ArticleController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\GameController;
use App\Http\Controllers\HomeController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

//Route::get('/getCharacters', [GameController::class, 'getScenarioCharacters']);
Route::get('/cron', [GameController::class, 'cron']);
Route::get('/test', [GameController::class, 'test']);




// AUTH Routes //
Route::post('/login', [AuthController::class, 'login']);
Route::post('/register', [AuthController::class, 'register']);
Route::post('/forgot/password', [AuthController::class, 'forgotPassword']);
Route::post('/forgot/password/code', [AuthController::class, 'forgotPasswordSendCode']);


Route::get('/articles', [ArticleController::class, 'index']);
Route::get('/articles/archive', [ArticleController::class, 'archive']);
Route::get('/article/{slug}', [ArticleController::class, 'article']);
Route::get('/leaderboard', [HomeController::class, 'leaderboard']);
Route::get('/games/leaderboard', [HomeController::class, 'gamesLeaderboard']);
Route::post('/search', [HomeController::class, 'search']);
Route::get('/games/archive', [GameController::class, 'archive']);
Route::get('/questions', [HomeController::class, 'questions']);
Route::get('/articles/comments', [ArticleController::class, 'comments']);






Route::get('/game/payment/verify/{id}', [GameController::class, 'gamePaymentVerify']);

Route::get('/leader', function (){
    return "Hello World";
});
Route::group(['middleware' => 'auth:sanctum'] , function () {
    Route::get('/user', function (Request $request) {return $request->user()->with('');});
    Route::post('/user/nickname', [DashboardController::class, 'nickname']);
    Route::post('/user/update', [DashboardController::class, 'update']);
    Route::post('/user/avatar', [DashboardController::class, 'avatar']);
    Route::get('/user/transactions', [DashboardController::class, 'transactions']);
    Route::get('/user/history', [DashboardController::class, 'history']);

    Route::post('/article/like', [ArticleController::class, 'like']);
    Route::post('/article/comment/add', [ArticleController::class, 'commentAdd']);

    Route::post('/game/reserve', [GameController::class, 'reserve']);
    Route::post('/game/edit', [GameController::class, 'gameEdit']);
    Route::post('/game/setting', [GameController::class, 'settingEdit']);
    Route::post('/game/scores', [GameController::class, 'scoresEdit']);
    Route::post('/game/user/remove', [GameController::class, 'gameUserRemove']);
    Route::post('/game/send/characters', [GameController::class, 'sendUserCharacters']);
    Route::post('/game/change/characters', [GameController::class, 'changeCharacters']);
    Route::post('/game/payment/attempt', [GameController::class, 'gamePayAttempt']);
    Route::post('/game/reserve/attempt', [GameController::class, 'noPaymentReserve']);

    Route::get('/game/payment/status/{id}', [GameController::class, 'gamePaymentStatus']);
    Route::post('/choose/user/chair', [GameController::class, 'chooseUserChair']);



    Route::post('/user/verify', [AuthController::class, 'userVerify']);
    Route::post('/user/send/code', [AuthController::class, 'userSendCode']);
    Route::post('/forgot/password/reset', [AuthController::class, 'forgotPasswordChange']);


    Route::get('/admin/users', [AdminController::class, 'users']);
    Route::get('/admin/users/{id}', [AdminController::class, 'user']);
    Route::post('/admin/user/update/{id}', [AdminController::class, 'userUpdate']);
    Route::post('/admin/user/password/{id}', [AdminController::class, 'password']);
    Route::get('/admin/questions', [AdminController::class, 'questions']);
    Route::post('/admin/question/add', [AdminController::class, 'questionAdd']);
    Route::post('/game/roles/visit', [GameController::class, 'roleVisits']);
    Route::get('/game/visit/logs', [GameController::class, 'roleVisitLogs']);
});

Route::post('/find/user', [GameController::class, 'user']);
Route::get('/games', [GameController::class, 'index']);
Route::get('/games/{id}', [GameController::class, 'single']);

// delete
Route::get('/create-games', [GameController::class, 'cron']);


Route::group(['middleware' => 'auth:sanctum'], function(){
    Route::post('user', [AuthController::class, 'user']);
});
