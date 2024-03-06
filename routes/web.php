<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});


Route::get('/test', function () {
    return view('test');
});

Auth::routes();

Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');

Route::get('/game', [App\Http\Controllers\GameController::class, 'start'])->middleware('auth')->name('game');
Route::POST('/game/check-answer', [App\Http\Controllers\GameController::class, 'checkAnswer'])->middleware('auth')->name('check-answer');
Route::POST('/game/get-game-score', [App\Http\Controllers\GameController::class, 'getGameScore'])->middleware('auth')->name('get-game-score');
